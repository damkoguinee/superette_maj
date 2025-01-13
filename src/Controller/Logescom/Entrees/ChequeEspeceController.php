<?php

namespace App\Controller\Logescom\Entrees;

use App\Entity\LieuxVentes;
use App\Entity\ChequeEspece;
use App\Form\ChequeEspeceType;
use App\Entity\MouvementCaisse;
use App\Repository\UserRepository;
use App\Repository\DeviseRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChequeEspeceRepository;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Repository\ModePaiementRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\MouvementCollaborateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/entrees/cheque/espece')]
class ChequeEspeceController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_entrees_cheque_espece_index', methods: ['GET'])]
    public function index(ChequeEspeceRepository $chequeEspeceRep, UserRepository $userRep, Request $request, ChequeEspeceRepository $chequeEspeceRepository, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("id_client_search")){
            $search = $request->get("id_client_search");
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

        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $clients = $userRep->findUserSearchByLieu($search, $lieu_vente);    
            $response = [];
            foreach ($clients as $client) {
                $response[] = [
                    'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                    'id' => $client->getId()
                ]; // Mettez à jour avec le nom réel de votre propriété
            }
            return new JsonResponse($response);
        }
        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("id_client_search")){
            $versements = $chequeEspeceRep->findVersementByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 15);
        }else{
            $versements = $chequeEspeceRep->findVersementByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 15);
        }
        return $this->render('logescom/entrees/cheque_espece/index.html.twig', [
            'versements' => $versements,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_entrees_cheque_espece_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, ChequeEspeceRepository $chequeEspeceRep, CompteOperationRepository $compteOpRep, CategorieOperationRepository $catetgorieOpRep, MouvementCaisseRepository $mouvementRep, DeviseRepository $deviseRep, MouvementCollaborateurRepository $mouvementCollabRep, UserRepository $userRep, ModePaiementRepository $modePaieRep, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("id_client_search")){
            $client_find = $userRep->find($request->get("id_client_search"));
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }else{
            $client_find = array();
            $soldes_collaborateur = array();
        }

        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $clients = $userRep->findUserSearchByLieu($search, $lieu_vente);    
            $response = [];
            foreach ($clients as $client) {
                $response[] = [
                    'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                    'id' => $client->getId()
                ]; // Mettez à jour avec le nom réel de votre propriété
            }
            return new JsonResponse($response);
        }
        $chequeEspece = new ChequeEspece();
        $form = $this->createForm(ChequeEspeceType::class, $chequeEspece, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montant_string = $form->get('montantCheque')->getData();
            $montant_string = preg_replace('/[^0-9,.]/', '', $montant_string);
            $montant_cheque = floatval($montant_string);

            $montant_string_recu = $form->get('montantRecu')->getData();
            $montant_string_recu = preg_replace('/[^0-9,.]/', '', $montant_string_recu);
            $montant_recu = floatval($montant_string_recu);
            $modePaie = $modePaieRep->find(4);

            $caisse = $form->getViewData()->getCaisseRetrait();
            $devise = $deviseRep->find(1);          
            $solde_caisse = $mouvementRep->findSoldeCaisse($caisse, $devise);
            if ($solde_caisse >= $montant_recu) {

                $client = $request->get('client');
                $client = $userRep->find($client);

                $chequeEspece->setLieuVente($lieu_vente)
                            ->setCollaborateur($client)
                            ->setSaisiePar($this->getUser())
                            ->setMontantCheque($montant_cheque)
                            ->setMontantRecu($montant_recu)
                            ->setDateSaisie(new \DateTime("now"));
                $mouvement_caisse_depot = new MouvementCaisse();
                $categorie_op = $catetgorieOpRep->find(4);
                $compte_op = $compteOpRep->find(4);
                $mouvement_caisse_depot->setCategorieOperation($categorie_op)
                        ->setCompteOperation($compte_op)
                        ->setTypeMouvement("versement")
                        ->setMontant($montant_cheque)
                        ->setDevise($devise)
                        ->setCaisse($form->getViewData()->getCaisseDepot())
                        ->setLieuVente($lieu_vente)
                        ->setSaisiePar($this->getUser())
                        ->setModePaie($modePaie)
                        ->setNumeroPaie($form->getViewData()->getNumeroCheque())
                        ->setBanqueCheque($form->getViewData()->getBanqueCheque())
                        ->setDateOperation($form->getViewData()->getDateOperation())
                        ->setDateSaisie(new \DateTime("now"));
                $chequeEspece->addMouvementCaiss($mouvement_caisse_depot);

                $mouvement_caisse_retrait = new MouvementCaisse();
                $categorie_op = $catetgorieOpRep->find(3);
                $compte_op = $compteOpRep->find(1);
                $mouvement_caisse_retrait->setCategorieOperation($categorie_op)
                        ->setCompteOperation($compte_op)
                        ->setTypeMouvement("decaissement")
                        ->setMontant(-$montant_recu)
                        ->setModePaie($modePaieRep->find(1))
                        ->setDevise($devise)
                        ->setSaisiePar($this->getUser())
                        ->setCaisse($form->getViewData()->getCaisseRetrait())
                        ->setLieuVente($lieu_vente)
                        ->setDateOperation($form->getViewData()->getDateOperation())
                        ->setDateSaisie(new \DateTime("now"));
                $chequeEspece->addMouvementCaiss($mouvement_caisse_retrait);

                $mouvement_collab_depot = new MouvementCollaborateur();
                $mouvement_collab_depot->setCollaborateur($client)
                        ->setOrigine("versement")
                        ->setMontant($montant_cheque)
                        ->setDevise($devise)
                        ->setCaisse($form->getViewData()->getCaisseDepot())
                        ->setLieuVente($lieu_vente)
                        ->setTraitePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateOperation())
                        ->setDateSaisie(new \DateTime("now"));
                $chequeEspece->addMouvementCollaborateur($mouvement_collab_depot);

                $mouvement_collab_retrait = new MouvementCollaborateur();
                $mouvement_collab_retrait->setCollaborateur($client)
                        ->setOrigine("decaissement")
                        ->setMontant(-$montant_recu)
                        ->setDevise($devise)
                        ->setCaisse($form->getViewData()->getCaisseRetrait())
                        ->setLieuVente($lieu_vente)
                        ->setTraitePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateOperation())
                        ->setDateSaisie(new \DateTime("now"));
                $chequeEspece->addMouvementCollaborateur($mouvement_collab_retrait);
                
                $entityManager->persist($chequeEspece);
                $entityManager->flush();

                $this->addFlash("success", "l'opération à été enregistrée avec succès :)");
                return $this->redirectToRoute('app_logescom_entrees_cheque_espece_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/entrees/cheque_espece/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'cheque_espece' => $chequeEspece,
                        'referer' => $referer,
                        'client_find' => $client_find,
                        'soldes_collaborateur' => $soldes_collaborateur,
                    ]);
                }
            }
            
        }

        return $this->render('logescom/entrees/cheque_espece/new.html.twig', [
            'cheque_espece' => $chequeEspece,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'client_find' => $client_find,
            'soldes_collaborateur' => $soldes_collaborateur,

        ]);
    }

    #[Route('show/{id}/{lieu_vente}', name: 'app_logescom_entrees_cheque_espece_show', methods: ['GET'])]
    public function show(ChequeEspece $chequeEspece, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/entrees/cheque_espece/show.html.twig', [
            'cheque_espece' => $chequeEspece,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_entrees_cheque_espece_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ChequeEspece $chequeEspece, ChequeEspeceRepository $chequeEspeceRep, EntityManagerInterface $entityManager, UserRepository $userRep, MouvementCollaborateurRepository $mouvementCollabRep, MouvementCaisseRepository $mouvementCaisseRep, DeviseRepository $deviseRep, MouvementCaisseRepository $mouvementRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("id_client_search")){
            $client_find = $userRep->find($request->get("id_client_search"));
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }else{
            $client_find = $chequeEspece->getCollaborateur();
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }

        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $clients = $userRep->findUserSearchByLieu($search, $lieu_vente);    
            $response = [];
            foreach ($clients as $client) {
                $response[] = [
                    'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                    'id' => $client->getId()
                ]; // Mettez à jour avec le nom réel de votre propriété
            }
            return new JsonResponse($response);
        }

        $form = $this->createForm(ChequeEspeceType::class, $chequeEspece, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montant_string = $form->get('montantCheque')->getData();
            $montant_string = preg_replace('/[^0-9,.]/', '', $montant_string);
            $montant_cheque = floatval($montant_string);

            $montant_string_recu = $form->get('montantRecu')->getData();
            $montant_string_recu = preg_replace('/[^0-9,.]/', '', $montant_string_recu);
            $montant_recu = floatval($montant_string_recu);

            $caisse = $form->getViewData()->getCaisseRetrait();
            $devise = $deviseRep->find(1);          
            $solde_caisse = $mouvementRep->findSoldeCaisse($caisse, $devise);
            if ($solde_caisse >= $montant_recu) {

                $client = $request->get('client');
                $client = $userRep->find($client);

                $chequeEspece->setLieuVente($lieu_vente)
                            ->setCollaborateur($client)
                            ->setSaisiePar($this->getUser())
                            ->setMontantCheque($montant_cheque)
                            ->setMontantRecu($montant_recu)
                            ->setDateSaisie(new \DateTime("now"));

                $mouvement_caisse_depot = $mouvementCaisseRep->findOneBy(['chequeEspece' => $chequeEspece]);
                $mouvement_caisse_depot->setMontant($montant_cheque)
                        ->setCaisse($form->getViewData()->getCaisseDepot())
                        ->setLieuVente($lieu_vente)
                        ->setSaisiePar($this->getUser())
                        ->setModePaie($modePaie)
                        ->setNumeroPaie($form->getViewData()->getNumeroCheque())
                        ->setBanqueCheque($form->getViewData()->getBanqueCheque())
                        ->setDateOperation($form->getViewData()->getDateOperation())
                        ->setDateSaisie(new \DateTime("now"));
                $chequeEspece->addMouvementCaiss($mouvement_caisse_depot);

                $mouvement_caisse_retrait = $mouvementCaisseRep->findOneBy(['chequeEspece' => $chequeEspece]);
                $mouvement_caisse_retrait->setMontant(-$montant_recu)
                        ->setDevise($devise)
                        ->setCaisse($form->getViewData()->getCaisseRetrait())
                        ->setLieuVente($lieu_vente)
                        ->setSaisiePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateOperation())
                        ->setDateSaisie(new \DateTime("now"));
                $chequeEspece->addMouvementCaiss($mouvement_caisse_retrait);

                $mouvement_collab_depot = $mouvementCollabRep->findOneBy(['chequeEspece' => $chequeEspece]);
                $mouvement_collab_depot->setCollaborateur($client)
                        ->setMontant($montant_cheque)
                        ->setCaisse($form->getViewData()->getCaisseDepot())
                        ->setLieuVente($lieu_vente)
                        ->setTraitePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateOperation())
                        ->setDateSaisie(new \DateTime("now"));
                $chequeEspece->addMouvementCollaborateur($mouvement_collab_depot);

                $mouvement_collab_retrait = $mouvementCollabRep->findOneBy(['chequeEspece' => $chequeEspece]);
                $mouvement_collab_retrait->setCollaborateur($client)
                        ->setMontant(-$montant_recu)
                        ->setCaisse($form->getViewData()->getCaisseRetrait())
                        ->setLieuVente($lieu_vente)
                        ->setTraitePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateOperation())
                        ->setDateSaisie(new \DateTime("now"));
                $chequeEspece->addMouvementCollaborateur($mouvement_collab_retrait);
                
                $entityManager->persist($chequeEspece);
                $entityManager->flush();

                $this->addFlash("success", "l'opération à été modifiée avec succès :)");
                return $this->redirectToRoute('app_logescom_entrees_cheque_espece_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/entrees/cheque_espece/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'cheque_espece' => $chequeEspece,
                        'referer' => $referer,
                        'client_find' => $client_find,
                        'soldes_collaborateur' => $soldes_collaborateur,
                    ]);
                }
            }
            
        }

        return $this->render('logescom/entrees/cheque_espece/edit.html.twig', [
            'cheque_espece' => $chequeEspece,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'client_find' => $client_find,
            'soldes_collaborateur' => $soldes_collaborateur
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_entrees_cheque_espece_delete', methods: ['POST'])]
    public function delete(Request $request, ChequeEspece $chequeEspece, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chequeEspece->getId(), $request->request->get('_token'))) {
            $entityManager->remove($chequeEspece);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_entrees_cheque_espece_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}
