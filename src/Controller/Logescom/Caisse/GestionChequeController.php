<?php

namespace App\Controller\Logescom\Caisse;

use App\Entity\Decaissement;
use App\Entity\GestionCheque;
use App\Entity\LieuxVentes;
use App\Entity\MouvementCaisse;
use App\Entity\MouvementCollaborateur;
use App\Entity\TransfertFond;
use App\Repository\CaisseRepository;
use App\Repository\CategorieOperationRepository;
use App\Repository\CompteOperationRepository;
use App\Repository\DeviseRepository;
use App\Repository\UserRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\GestionChequeRepository;
use App\Repository\LieuxVentesRepository;
use App\Repository\ModePaiementRepository;
use App\Repository\MouvementCaisseRepository;
use App\Repository\TransfertFondRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/caisse/gestion/cheque')]
class GestionChequeController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_caisse_gestion_cheque_index')]
    public function index(MouvementCaisseRepository $mouvementCaisseRep, CaisseRepository $caisseRep, UserRepository $userRep, Request $request, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("search_cheque")){
            $search = $request->get("search_cheque");
        }else{
            $search = "";
        }

        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        $pageEncours = $request->get('pageEncours', 1);
        $caisses = $caisseRep->findCaisseByLieuByType($lieu_vente, 'caisse');
        if ($request->get("search_cheque")){
            $cheques = $mouvementCaisseRep->ListeDesChequesCaissesParLieuParDateParChequePagine($lieu_vente, $search, $date1, $date2, $pageEncours, 25);
        }else{
            $cheques = $mouvementCaisseRep->ListeDesChequesCaissesParLieuParDateParPagine($lieu_vente, $caisses, $date1, $date2, $pageEncours, 25);
        }

        $pageEncours = $request->get('pageEncoursTraites', 1);
        $cheques_traites =  $mouvementCaisseRep->ListeDesChequesCaissesTraitesParLieuParDatePagine($lieu_vente, $date1, $date2, $pageEncours, 25);

        return $this->render('logescom/caisse/gestion_cheque/index.html.twig', [
            'cheques' => $cheques,
            'cheques_traites' => $cheques_traites,
            'search' => $search,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'date1' => $date1,
            'date2' => $date2,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_caisse_gestion_cheque_show')]
    public function show(MouvementCaisse $mouvementCaisse, MouvementCaisseRepository $mouvementCaisseRep, CaisseRepository $caisseRep, UserRepository $userRep, Request $request, CategorieOperationRepository $catetgorieOpRep, CompteOperationRepository $compteOpRep, TransfertFondRepository $transfertRep, LieuxVentes $lieu_vente, LieuxVentesRepository $lieuVenteRep, DeviseRepository $deviseRep, GestionChequeRepository $gestionChequeRep, EntrepriseRepository $entrepriseRep, ModePaiementRepository $modePaieRep, EntityManagerInterface $em): Response
    {
        $categorie_op = $catetgorieOpRep->find(3);
        $compte_op = $compteOpRep->find(1);

        $gestion_cheque = $gestionChequeRep->findOneBy(['mouvementCaisse' => $mouvementCaisse]);

        if ($request->get('caisse_recep')) {
            $caisse_recep = $caisseRep->find($request->get('caisse_recep'));
            if ($gestion_cheque) {
                $gestion_cheque->setMontant($mouvementCaisse->getMontant())
                        ->setCaisseDepart($mouvementCaisse->getCaisse())
                        ->setCaisseReception($caisse_recep)
                        ->setLieuVenteDepart($lieu_vente)
                        ->setLieuVenteReception($lieu_vente)
                        ->setEnvoyePar($this->getUser())
                        ->setTraitePar($this->getUser())
                        ->setDateOperation(new \DateTime("now"))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateReception(new \DateTime("now"))
                        ->setEtat("traité");
            }else{
                $gestion_cheque = new GestionCheque();
                $gestion_cheque->setMontant($mouvementCaisse->getMontant())
                        ->setCaisseDepart($mouvementCaisse->getCaisse())
                        ->setCaisseReception($caisse_recep)
                        ->setLieuVenteDepart($lieu_vente)
                        ->setLieuVenteReception($lieu_vente)
                        ->setEnvoyePar($this->getUser())
                        ->setTraitePar($this->getUser())
                        ->setDateOperation(new \DateTime("now"))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateReception(new \DateTime("now"))
                        ->setEtat("traité");
                $mouvementCaisse->addGestionCheque($gestion_cheque);
            }
                    
            $mouvementCaisse->setEtatOperation("traité")
                    ->setDateSortie(new \DateTime("now"))
                    ->setDetailSortie("chèque transféré de : " .$mouvementCaisse->getCaisse()->getDesignation()." vers ".$caisse_recep->getDesignation())
                    ->setDateOperation(new \DateTime("now"))
                    ->setDateSaisie(new \DateTime("now"))
                    ->setSaisiePar($this->getUser())
                    ->setCaisse($caisse_recep)
                    ;
            $em->persist($mouvementCaisse);
            $em->flush();

            $this->addFlash("success", "Transfert enregistré avec succès :)");
            return $this->redirectToRoute('app_logescom_caisse_gestion_cheque_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($request->get('caisse_recep_especes')) {
            $caisse_recep_especes = $caisseRep->find($request->get('caisse_recep_especes'));
            if ($gestion_cheque) {
                $gestion_cheque->setMontant($mouvementCaisse->getMontant())
                        ->setCaisseDepart($mouvementCaisse->getCaisse())
                        ->setCaisseReception($caisse_recep_especes)
                        ->setLieuVenteDepart($lieu_vente)
                        ->setLieuVenteReception($lieu_vente)
                        ->setEnvoyePar($this->getUser())
                        ->setTraitePar($this->getUser())
                        ->setDateOperation(new \DateTime("now"))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateReception(new \DateTime("now"))
                        ->setEtat("traité");
            }else{
                $gestion_cheque = new GestionCheque();
                $gestion_cheque->setMontant($mouvementCaisse->getMontant())
                        ->setCaisseDepart($mouvementCaisse->getCaisse())
                        ->setCaisseReception($caisse_recep_especes)
                        ->setLieuVenteDepart($lieu_vente)
                        ->setLieuVenteReception($lieu_vente)
                        ->setEnvoyePar($this->getUser())
                        ->setTraitePar($this->getUser())
                        ->setDateOperation(new \DateTime("now"))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateReception(new \DateTime("now"))
                        ->setEtat("traité");
                $mouvementCaisse->addGestionCheque($gestion_cheque);
            }
                    
            $mouvementCaisse->setEtatOperation("traité")
                    ->setDateSortie(new \DateTime("now"))
                    ->setDetailSortie("chèque espèces : " .$mouvementCaisse->getCaisse()->getDesignation()." vers ".$caisse_recep_especes->getDesignation())
                    ->setDateOperation(new \DateTime("now"))
                    ->setDateSaisie(new \DateTime("now"))
                    ->setModePaie($modePaieRep->find(1))
                    ->setSaisiePar($this->getUser())
                    ->setCaisse($caisse_recep_especes)
                    ;
            $em->persist($mouvementCaisse);
            $em->flush();

            $this->addFlash("success", "Transfert enregistré avec succès :)");
            return $this->redirectToRoute('app_logescom_caisse_gestion_cheque_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($request->get('lieu_recep')) {
            $lieu_recep = $lieuVenteRep->find($request->get('lieu_recep'));
            

            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('ymd');
            $idSuivant =($transfertRep->findMaxId() + 1);
            $reference = "trans".$referenceDate . sprintf('%04d', $idSuivant);

            if ($gestion_cheque) {
                $gestion_cheque->setMontant($mouvementCaisse->getMontant())
                        ->setCaisseDepart($mouvementCaisse->getCaisse())
                        ->setLieuVenteDepart($lieu_vente)
                        ->setLieuVenteReception($lieu_recep)
                        ->setEnvoyePar($this->getUser())
                        ->setDateOperation(new \DateTime("now"))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateReception(new \DateTime("now"))
                        ->setEtat("non traité");
            }else{
                
                $gestion_cheque = new GestionCheque();
                $gestion_cheque->setMontant($mouvementCaisse->getMontant())
                        ->setCaisseDepart($mouvementCaisse->getCaisse())
                        ->setLieuVenteDepart($lieu_vente)
                        ->setLieuVenteReception($lieu_recep)
                        ->setEnvoyePar($this->getUser())
                        ->setDateOperation(new \DateTime("now"))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateReception(new \DateTime("now"))
                        ->setEtat("non traité");
                $mouvementCaisse->addGestionCheque($gestion_cheque);
            }
                    
            $mouvementCaisse->setEtatOperation("en attente")
                    ->setDateSortie(new \DateTime("now"))
                    ->setDetailSortie("chèque transféré de : " .$mouvementCaisse->getCaisse()->getDesignation()." vers ".$lieu_recep->getLieu()." ")
                    ->setDateOperation(new \DateTime("now"))
                    ->setDateSaisie(new \DateTime("now"))
                    ->setSaisiePar($this->getUser())
                    ;
            $em->persist($mouvementCaisse);
            $em->flush();

            $this->addFlash("success", "Transfert enregistré avec succès :)");
            return $this->redirectToRoute('app_logescom_caisse_gestion_cheque_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($request->get('collaborateur')) {
            $collaborateur = $userRep->find($request->get('collaborateur'));

            if ($gestion_cheque) {
                $gestion_cheque->setMontant($mouvementCaisse->getMontant())
                        ->setCaisseDepart($mouvementCaisse->getCaisse())
                        ->setCaisseReception(null)
                        ->setCollaborateur($collaborateur)
                        ->setLieuVenteDepart($lieu_vente)
                        ->setLieuVenteReception(null)
                        ->setEnvoyePar($this->getUser())
                        ->setTraitePar($this->getUser())
                        ->setDateOperation(new \DateTime("now"))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateReception(new \DateTime("now"))
                        ->setEtat("traité");
            }else{
                $gestion_cheque = new GestionCheque();
                $gestion_cheque->setMontant($mouvementCaisse->getMontant())
                        ->setCaisseDepart($mouvementCaisse->getCaisse())
                        ->setCaisseReception(null)
                        ->setCollaborateur($collaborateur)
                        ->setLieuVenteDepart($lieu_vente)
                        ->setLieuVenteReception(null)
                        ->setEnvoyePar($this->getUser())
                        ->setTraitePar($this->getUser())
                        ->setDateOperation(new \DateTime("now"))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateReception(new \DateTime("now"))
                        ->setEtat("traité");
                $mouvementCaisse->addGestionCheque($gestion_cheque);
            }
                    
           
            $decaissement = new Decaissement();
            $mouvementCollab = new MouvementCollaborateur();
            $mouvementCollab->setCollaborateur($collaborateur)
                ->setOrigine("transfert cheque")
                ->setMontant(-$mouvementCaisse->getMontant())
                ->setDevise($deviseRep->find(1))
                ->setCaisse($mouvementCaisse->getCaisse())
                ->setLieuVente($lieu_vente)
                ->setTraitePar($this->getUser())
                ->setDateOperation(new \DateTime("now"))
                ->setDateSaisie(new \DateTime("now"));
            $gestion_cheque->addMouvementCollaborateur($mouvementCollab);
            $em->persist($gestion_cheque);

            $mouvementCaisse->setEtatOperation("traité")
            ->setDateSortie(new \DateTime("now"))
            ->setDetailSortie("chèque transféré de : " .$mouvementCaisse->getCaisse()->getDesignation()." vers ".$collaborateur->getPrenom()." ".$collaborateur->getNom())
            ->setDateOperation(new \DateTime("now"))
            ->setDateSaisie(new \DateTime("now"))
            ->setSaisiePar($this->getUser())
            ->setMontant(0)
            ;
            $em->persist($mouvementCaisse);
            $em->flush();

            $this->addFlash("success", "Transfert enregistré avec succès :)");
            return $this->redirectToRoute('app_logescom_caisse_gestion_cheque_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($request->get("annuler")) {
            $caisse = $caisseRep->findCaisseByLieuByType($lieu_vente, 'caisse');
            $caisse = $caisseRep->findOneBy(['id' => $caisse]);

            $gestion_cheque = $gestionChequeRep->findOneBy(['mouvementCaisse' => $mouvementCaisse]);

            $em->remove($gestion_cheque);

            $mouvementCaisse->setEtatOperation("non traité")
                    ->setDateSortie(null)
                    ->setDetailSortie(null)
                    ->setMontant($gestion_cheque->getMontant())
                    ->setCaisse($caisse)
                    ->setModePaie($modePaieRep->find(4));
            $em->persist($mouvementCaisse);
            $em->flush();

            $this->addFlash("success", "Transfert annulé avec succès :)");
            return $this->redirectToRoute('app_logescom_caisse_gestion_cheque_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $clients = $userRep->findUserSearchByLieu($search, $lieu_vente);    
            $response = [];
            foreach ($clients as $client) {
                $response[] = [
                    'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                    'id' => $client->getId()
                ]; 
            }
            return new JsonResponse($response);
        }

        if ($request->get("id_client_search")){
            $collaborateur = $userRep->find($request->get("id_client_search"));;
        }else{
            $collaborateur = array();
        }

        $id_lieu_vente = $lieu_vente->getId();
        return $this->render('logescom/caisse/gestion_cheque/show.html.twig', [
            'cheque' => $mouvementCaisse,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'banques' => $caisseRep->findCaisseByLieu($lieu_vente),
            'lieux' => $lieuVenteRep->findAllLieuxVenteExecept($id_lieu_vente),
            'collaborateur' => $collaborateur,
        ]);
    }

    #[Route('/confirmation/{lieu_vente}', name: 'app_logescom_caisse_gestion_cheque_confirmation')]
    public function confirmation(MouvementCaisseRepository $mouvementCaisseRep, CaisseRepository $caisseRep, UserRepository $userRep, Request $request, CategorieOperationRepository $catetgorieOpRep, CompteOperationRepository $compteOpRep, TransfertFondRepository $transfertRep, LieuxVentes $lieu_vente, LieuxVentesRepository $lieuVenteRep, GestionChequeRepository $gestionChequeRep, EntrepriseRepository $entrepriseRep, EntityManagerInterface $em): Response
    {
        if ($request->get("search_cheque")){
            $search = $request->get("search_cheque");
        }else{
            $search = "";
        }

        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        $confirmation_cheques = $gestionChequeRep->findBy(['lieuVenteReception' => $lieu_vente, 'etat' => 'non traité']);

        
        $id_lieu_vente = $lieu_vente->getId();
        return $this->render('logescom/caisse/gestion_cheque/confirmation_cheque.html.twig', [
            'cheques' => $confirmation_cheques,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'caisses' => $caisseRep->findCaisseByLieu($lieu_vente),
            'lieux' => $lieuVenteRep->findAllLieuxVenteExecept($id_lieu_vente),
            'search' => $search,
            'date1' => $date1,
            'date2' => $date2,
        ]);
    }

    #[Route('/confirmation/{id}/{lieu_vente}', name: 'app_logescom_caisse_gestion_cheque_confirmation_validation')]
    public function confirmationValidation(GestionCheque $gestionCheque, MouvementCaisseRepository $mouvementCaisseRep, CaisseRepository $caisseRep, UserRepository $userRep, Request $request, CategorieOperationRepository $catetgorieOpRep, CompteOperationRepository $compteOpRep, TransfertFondRepository $transfertRep, LieuxVentes $lieu_vente, LieuxVentesRepository $lieuVenteRep, GestionChequeRepository $gestionChequeRep, EntrepriseRepository $entrepriseRep, EntityManagerInterface $em): Response
    {

        $categorie_op = $catetgorieOpRep->find(3);
        $compte_op = $compteOpRep->find(1);
        if ($request->get('confirmer')) {
            $caisse_recep = $caisseRep->find($request->get('caisse_recep'));  
            $type_caisse = $caisse_recep->getType(); 

            $gestionCheque->setEtat($type_caisse == "caisse" ? "en attente" : "traité")
                    ->setCaisseReception($caisse_recep)
                    ->setTraitePar($this->getUser())
                    ->setDateReception(new \DateTime("now"));
            $em->persist($gestionCheque);

            $mouvementCaisse = $gestionCheque->getMouvementCaisse();
            $mouvementCaisse->setEtatOperation($type_caisse == "caisse" ? "non traité" : "traité")
                    ->setDateSortie(new \DateTime("now"))
                    ->setDetailSortie("chèque transféré de : " .$mouvementCaisse->getCaisse()->getDesignation()." vers ".$caisse_recep->getDesignation())
                    ->setDateOperation(new \DateTime("now"))
                    ->setDateSaisie(new \DateTime("now"))
                    ->setSaisiePar($this->getUser())
                    ->setLieuVente($lieu_vente)
                    ->setCaisse($caisse_recep);
            $em->persist($mouvementCaisse);
            $em->flush();

            $this->addFlash("success", "Transfert econfirmé avec succès :)");
            return $this->redirectToRoute('app_logescom_caisse_gestion_cheque_confirmation', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        $confirmation_cheques = $gestionChequeRep->findBy(['lieuVenteReception' => $lieu_vente, 'etat' => 'non traité']);
        
        $id_lieu_vente = $lieu_vente->getId();
        return $this->render('logescom/caisse/gestion_cheque/confirmation_cheque.html.twig', [
            'cheques' => $confirmation_cheques,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'caisses' => $caisseRep->findCaisseByLieu($lieu_vente),
            'lieux' => $lieuVenteRep->findAllLieuxVenteExecept($id_lieu_vente),
        ]);
    }
}
