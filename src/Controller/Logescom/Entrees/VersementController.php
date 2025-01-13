<?php

namespace App\Controller\Logescom\Entrees;

use App\Entity\Versement;
use App\Entity\LieuxVentes;
use App\Form\VersementType;
use App\Entity\Modification;
use App\Entity\MouvementCaisse;
use App\Entity\DeleteDecaissement;
use App\Repository\UserRepository;
use App\Repository\DeviseRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\VersementRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use App\Repository\ModificationRepository;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Repository\ConfigurationLogicielRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\MouvementCollaborateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/logescom/entrees/versement')]
class VersementController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_entrees_versement_index', methods: ['GET'])]
    public function index(VersementRepository $versementRepository, UserRepository $userRep, Request $request, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
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
            $versements = $versementRepository->findVersementByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 25);
        }else{
            $versements = $versementRepository->findVersementByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 25);
        }

        return $this->render('logescom/entrees/versement/index.html.twig', [
            'versements' => $versements,
            'search' => $search,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_entrees_versement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, VersementRepository $versementRep, CompteOperationRepository $compteOpRep, CategorieOperationRepository $catetgorieOpRep, DeviseRepository $deviseRep, MouvementCollaborateurRepository $mouvementCollabRep, UserRepository $userRep, ConfigurationLogicielRepository $configRep, EntrepriseRepository $entrepriseRep): Response
    {
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
        $config = $configRep->findOneBy([]);
        $versement = new Versement();
        $form = $this->createForm(VersementType::class, $versement, ['lieu_vente' => $lieu_vente, 'versement' => $versement]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval($montantString);
            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('ymd');
            $idSuivant =($versementRep->findMaxId() + 1);
            $reference = "vers".$referenceDate . sprintf('%04d', $idSuivant);
            $client = $request->get('client');
            $client = $userRep->find($client);

            $versement->setLieuVente($lieu_vente)
                        ->setClient($client)
                        ->setTraitePar($this->getUser())
                        ->setReference($reference)
                        ->setMontant($montant)
                        ->setDateSaisie(new \DateTime("now"));
            $mouvement_caisse = new MouvementCaisse();
            $categorie_op = $catetgorieOpRep->find(4);
            $compte_op = $compteOpRep->find(4);
            $mouvement_caisse->setCategorieOperation($categorie_op)
                    ->setCompteOperation($compte_op)
                    ->setTypeMouvement("versement")
                    ->setMontant($montant)
                    ->setDevise($form->getViewData()->getDevise())
                    ->setCaisse($form->getViewData()->getCompte())
                    ->setModePaie($form->getViewData()->getModePaie())
                    ->setNumeroPaie($form->getViewData()->getNumeroPaiement())
                    ->setBanqueCheque($form->getViewData()->getBanqueCheque())
                    ->setLieuVente($lieu_vente)
                    ->setSaisiePar($this->getUser())
                    ->setDateOperation($form->getViewData()->getDateversement())
                    ->setDateSaisie(new \DateTime("now"));
            $versement->addMouvementCaiss($mouvement_caisse);

            $mouvement_collab = new MouvementCollaborateur();

            $taux = $form->getViewData()->getTaux();
            if ($taux == 1) {
                $montant = $montant;
                $devise = $form->getViewData()->getDevise();
            }else{
                $montant = $montant * $taux;
                $devise = $deviseRep->find(1);
            }
            $mouvement_collab->setCollaborateur($client)
                    ->setOrigine("versement")
                    ->setMontant($montant)
                    ->setDevise($devise)
                    ->setCaisse($form->getViewData()->getCompte())
                    ->setLieuVente($lieu_vente)
                    ->setTraitePar($this->getUser())
                    ->setDateOperation($form->getViewData()->getDateversement())
                    ->setDateSaisie(new \DateTime("now"));
            $versement->addMouvementCollaborateur($mouvement_collab);
            
            $entityManager->persist($versement);
            $entityManager->flush();

            $this->addFlash("success", "versement enregistré avec succès :)");
            return $this->redirectToRoute('app_logescom_entrees_versement_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($request->get("id_client_search")){
            $client_find = $userRep->find($request->get("id_client_search"));
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }else{
            $client_find = array();
            $soldes_collaborateur = array();
        }

        return $this->render('logescom/entrees/versement/new.html.twig', [
            'versement' => $versement,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'client_find' => $client_find,
            'soldes_collaborateur' => $soldes_collaborateur,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_entrees_versement_show', methods: ['GET'])]
    public function show(Versement $versement, ModificationRepository $modificationRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $versements_modif = $modificationRep->findBy(['versement' => $versement]);
        return $this->render('logescom/entrees/versement/show.html.twig', [
            'versement' => $versement,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'versements_modif' => $versements_modif,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_entrees_versement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Versement $versement, VersementRepository $versementRep, EntityManagerInterface $entityManager, UserRepository $userRep, MouvementCollaborateurRepository $mouvementCollabRep, MouvementCaisseRepository $mouvementCaisseRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
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
        $versement_init = $versementRep->find($versement);
        $versement_modif = new Modification();
        $versement_modif->setCollaborateur($versement_init->getClient())
                        ->setMontant($versement_init->getMontant())
                        ->setCaisse($versement_init->getCompte())
                        ->setOrigine("versement")
                        ->setDevise($versement_init->getDevise())
                        ->setTraitePar($versement_init->getTraitePar())
                        ->setDateOperation($versement_init->getDateVersement())
                        ->setDateSaisie($versement_init->getDateSaisie())
                        ->setVersement($versement_init);
        $entityManager->persist($versement_modif);

        $form = $this->createForm(VersementType::class, $versement, ['lieu_vente' => $lieu_vente, 'versement' => $versement]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval($montantString);
            $client = $request->get('client');
            $client = $userRep->find($client);
            $versement->setMontant($montant)
                        ->setClient($client)
                        ->setTraitePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

            $mouvement_collab = $mouvementCollabRep->findOneBy(['versement' => $versement]); 
            $mouvement_collab->setCollaborateur($client)
                    ->setMontant($montant)
                    ->setDevise($form->getViewData()->getDevise())
                    ->setCaisse($form->getViewData()->getCompte())
                    ->setLieuVente($lieu_vente)
                    ->setTraitePar($this->getUser())
                    ->setDateOperation($form->getViewData()->getDateversement())
                    ->setDateSaisie(new \DateTime("now"));

            $mouvement_caisse = $mouvementCaisseRep->findOneBy(['versement' => $versement]); 
            $mouvement_caisse->setMontant($montant)
                    ->setDevise($form->getViewData()->getDevise())
                    ->setCaisse($form->getViewData()->getCompte())
                    ->setLieuVente($lieu_vente)
                    ->setSaisiePar($this->getUser())
                    ->setModePaie($form->getViewData()->getModePaie())
                    ->setNumeroPaie($form->getViewData()->getNumeroPaiement())
                    ->setBanqueCheque($form->getViewData()->getBanqueCheque())
                    ->setDateOperation($form->getViewData()->getDateversement())
                    ->setDateSaisie(new \DateTime("now"));
            $entityManager->persist($versement);
            $entityManager->persist($versement_modif);
            $entityManager->flush();
            $this->addFlash("success", "Versement modifié avec succès :)");
            return $this->redirectToRoute('app_logescom_entrees_versement_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            
        }

        if ($request->get("id_client_search")){
            $client_find = $userRep->find($request->get("id_client_search"));
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }else{
            $client_find = $versement->getClient();
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }

        return $this->render('logescom/entrees/versement/edit.html.twig', [
            'versement' => $versement,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'client_find' => $client_find,
            'soldes_collaborateur' => $soldes_collaborateur
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_entrees_versement_delete', methods: ['POST'])]
    public function delete(Request $request, Versement $versement, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$versement->getId(), $request->request->get('_token'))) {
            $delete_vers = new DeleteDecaissement();
            $delete_vers->setReference($versement->getReference())
                    ->setMontant($versement->getMontant())
                    ->setNumeroChequeBord($versement->getNumeroPaiement())
                    ->setBanqueCheque($versement->getBanqueCheque())
                    ->setDateSaisie(new \DateTime("now"))
                    ->setClient($versement->getClient()->getNom().' '.$versement->getClient()->getPrenom())
                    ->setTraitePar($versement->getTraitePar()->getPrenom().' '.$versement->getTraitePar()->getNom())
                    ->setDevise($versement->getDevise()->getNomDevise())
                    ->setCaisse($versement->getCompte()->getDesignation())
                    ->setDateDecaissement($versement->getDateVersement())
                    ->setCommentaire("suppression versement");
            $entityManager->persist($delete_vers);
            $entityManager->remove($versement);
            $entityManager->flush();

            $this->addFlash("success", "versement supprimé avec succès :)");
        }

        return $this->redirectToRoute('app_logescom_entrees_versement_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/pdf/reçu/{id}/{lieu_vente}', name: 'app_logescom_entrees_versement_recu_pdf', methods: ['GET'])]
    public function recuPdf(Versement $versement, LieuxVentes $lieu_vente, MouvementCollaborateurRepository $mouvementCollabRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep)
    {
        $entreprise = $entrepriseRep->findOneBy(['id' => 1]);
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$entreprise->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $soleCollaborateur = $mouvementCollabRep->findSoldeCollaborateur($versement->getClient());

        $collaborateur = $versement->getClient();
        $dateOp = $versement->getDateVersement();

        $ancienSoleCollaborateur = $mouvementCollabRep->findAncienSoldeCollaborateur($collaborateur, $dateOp);

        $html = $this->renderView('logescom/entrees/versement/recu_pdf.html.twig', [
            'versement' => $versement,
            'solde_collaborateur' => $soleCollaborateur,
            'ancien_solde' => $ancienSoleCollaborateur,
            'logoPath' => $logoBase64,
            'lieu_vente' => $lieu_vente,
            // 'qrCode'    => $qrCode,
        ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set("isPhpEnabled", true);
        $options->set("isHtml5ParserEnabled", true);

        // Instancier Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier (A4 par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF (stream le PDF au navigateur)
        $dompdf->render();

        // Renvoyer une réponse avec le contenu du PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="réçu_versement.pdf"',
        ]);
    }
}
