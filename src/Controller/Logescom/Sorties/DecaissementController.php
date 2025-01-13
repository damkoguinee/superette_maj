<?php

namespace App\Controller\Logescom\Sorties;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Client;
use App\Entity\LieuxVentes;
use App\Entity\Decaissement;
use App\Entity\Modification;
use App\Form\DecaissementType;
use App\Entity\MouvementCaisse;
use App\Entity\ModifDecaissement;
use App\Entity\DeleteDecaissement;
use App\Repository\UserRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use App\Repository\DecaissementRepository;
use App\Repository\ModificationRepository;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ModifDecaissementRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Repository\MouvementCollaborateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/sorties/decaissement')]
class DecaissementController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_sorties_decaissement_index', methods: ['GET'])]
    public function index(DecaissementRepository $decaissementRepository, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep, Request $request, UserRepository $userRep): Response
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
            $decaissements = $decaissementRepository->findDecaissementByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 25);
        }else{
            $decaissements = $decaissementRepository->findDecaissementByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 25);
        }

        return $this->render('logescom/sorties/decaissement/index.html.twig', [
            'decaissements' => $decaissements,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_sorties_decaissement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, DecaissementRepository $decaissementRep, CompteOperationRepository $compteOpRep, CategorieOperationRepository $catetgorieOpRep, MouvementCaisseRepository $mouvementRep, MouvementCollaborateurRepository $mouvementCollabRep, UserRepository $userRep, EntrepriseRepository $entrepriseRep): Response
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

        $decaissement = new Decaissement();
        $form = $this->createForm(DecaissementType::class, $decaissement, ['lieu_vente' => $lieu_vente, 'decaissement' => $decaissement] );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            // Supprimez les espaces pour obtenir un nombre valide
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            // Convertissez la chaîne en nombre
            $montant = floatval($montantString);
            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('ymd');
            $idSuivant =($decaissementRep->findMaxId() + 1);
            $reference = "dec".$referenceDate . sprintf('%04d', $idSuivant);
            $client = $request->get('client');
            $client = $userRep->find($client);
            $caisse = $form->getViewData()->getCompteDecaisser();
            $devise = $form->getViewData()->getDevise();
            $solde_caisse = $mouvementRep->findSoldeCaisse($caisse, $devise);
            // dd($solde_caisse);
            if ($solde_caisse >= $montant) {
                $decaissement->setLieuVente($lieu_vente)
                        ->setClient($client)
                        ->setTraitePar($this->getUser())
                        ->setReference($reference)
                        ->setMontant($montant)
                        ->setDateSaisie(new \DateTime("now"));

                $fichier = $form->get("document")->getData();
                if ($fichier) {
                    $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomFichier = $slugger->slug($nomFichier);
                    $nouveauNomFichier .="_".uniqid();
                    $nouveauNomFichier .= "." .$fichier->guessExtension();
                    $fichier->move($this->getParameter("dossier_decaissement"),$nouveauNomFichier);
                    $decaissement->setDocument($nouveauNomFichier);
                }

                $mouvement_collab = new MouvementCollaborateur();
                $mouvement_collab->setCollaborateur($client)
                        ->setOrigine("decaissement")
                        ->setMontant(-$montant)
                        ->setDevise($form->getViewData()->getDevise())
                        ->setCaisse($form->getViewData()->getCompteDecaisser())
                        ->setLieuVente($lieu_vente)
                        ->setTraitePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateDecaissement())
                        ->setDateSaisie(new \DateTime("now"));
                $decaissement->addMouvementCollaborateur($mouvement_collab);

                $mouvement_caisse = new MouvementCaisse();
                $categorie_op = $catetgorieOpRep->find(3);
                $compte_op = $compteOpRep->find(1);
                $mouvement_caisse->setCategorieOperation($categorie_op)
                        ->setCompteOperation($compte_op)
                        ->setTypeMouvement("decaissement")
                        ->setMontant(-$montant)
                        ->setDevise($form->getViewData()->getDevise())
                        ->setCaisse($form->getViewData()->getCompteDecaisser())
                        ->setModePaie($form->getViewData()->getModePaie())
                        ->setLieuVente($lieu_vente)
                        ->setSaisiePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateDecaissement())
                        ->setDateSaisie(new \DateTime("now"));
                $decaissement->addMouvementCaiss($mouvement_caisse);
                $entityManager->persist($decaissement);
                $entityManager->flush();

                $this->addFlash("success", "Décaissement enregistré avec succès :)");
                return $this->redirectToRoute('app_logescom_sorties_decaissement_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/sorties/decaissement/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'decaissement' => $decaissement,
                        'referer' => $referer,
                        'client_find' => $client_find,
                        'soldes_collaborateur' => $soldes_collaborateur,
                    ]);
                }
            }
        }
        
        return $this->render('logescom/sorties/decaissement/new.html.twig', [
            'decaissement' => $decaissement,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'client_find' => $client_find,
            'soldes_collaborateur' => $soldes_collaborateur,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_sorties_decaissement_show', methods: ['GET'])]
    public function show(Decaissement $decaissement, ModificationRepository $modificationRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $decaissement_modif = $modificationRep->findBy(['decaissement' => $decaissement]);
        return $this->render('logescom/sorties/decaissement/show.html.twig', [
            'decaissement' => $decaissement,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'decaissements_modif' => $decaissement_modif,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_sorties_decaissement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Decaissement $decaissement, DecaissementRepository $decaissementRep, EntityManagerInterface $entityManager, UserRepository $userRep, MouvementCollaborateurRepository $mouvementCollabRep, MouvementCaisseRepository $mouvementCaisseRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
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
        $decaissement_init = $decaissementRep->find($decaissement);
        $decaissement_modif = new Modification();
        $decaissement_modif->setCollaborateur($decaissement_init->getClient())
                        ->setMontant($decaissement_init->getMontant())
                        ->setCaisse($decaissement_init->getCompteDecaisser())
                        ->setOrigine("decaissement")
                        ->setDevise($decaissement_init->getDevise())
                        ->setTraitePar($decaissement_init->getTraitePar())
                        ->setDateOperation($decaissement_init->getDateDecaissement())
                        ->setDateSaisie($decaissement_init->getDateSaisie())
                        ->setDecaissement($decaissement_init);
        $entityManager->persist($decaissement_modif);
        $form = $this->createForm(DecaissementType::class, $decaissement, ['lieu_vente' => $lieu_vente, 'decaissement' => $decaissement] );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval($montantString);
            $caisse = $form->getViewData()->getCompteDecaisser();
            $devise = $form->getViewData()->getDevise();
            $solde_caisse = $mouvementCaisseRep->findSoldeCaisse($caisse, $devise);
            if ($solde_caisse >= $montant) {
                $client = $request->get('client');
                $client = $userRep->find($client);
                $decaissement->setMontant($montant)
                            ->setClient($client)
                            ->setTraitePar($this->getUser())
                            ->setDateSaisie(new \DateTime("now"));
                $justificatif =$form->get("document")->getData();
                if ($justificatif) {
                    if ($decaissement->getDocument()) {
                        $ancienJustificatif=$this->getParameter("dossier_decaissement")."/".$decaissement->getDocument();
                        if (file_exists($ancienJustificatif)) {
                            unlink($ancienJustificatif);
                        }
                    }
                    $nomJustificatif= pathinfo($justificatif->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomJustificatif = $slugger->slug($nomJustificatif);
                    $nouveauNomJustificatif .="_".uniqid();
                    $nouveauNomJustificatif .= "." .$justificatif->guessExtension();
                    $justificatif->move($this->getParameter("dossier_decaissement"),$nouveauNomJustificatif);
                    $decaissement->setDocument($nouveauNomJustificatif);

                }

                $mouvement_collab = $mouvementCollabRep->findOneBy(['decaissement' => $decaissement]); 
                $mouvement_collab->setCollaborateur($client)
                        ->setMontant(-$montant)
                        ->setDevise($form->getViewData()->getDevise())
                        ->setCaisse($form->getViewData()->getCompteDecaisser())
                        ->setLieuVente($lieu_vente)
                        ->setTraitePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateDecaissement())
                        ->setDateSaisie(new \DateTime("now"));

                $mouvement_caisse = $mouvementCaisseRep->findOneBy(['decaissement' => $decaissement]); 
                $mouvement_caisse->setMontant(-$montant)
                        ->setDevise($form->getViewData()->getDevise())
                        ->setCaisse($form->getViewData()->getCompteDecaisser())
                        ->setModePaie($form->getViewData()->getModePaie())
                        ->setLieuVente($lieu_vente)
                        ->setSaisiePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateDecaissement())
                        ->setDateSaisie(new \DateTime("now"));

                $entityManager->persist($decaissement);
                $entityManager->persist($decaissement_modif);
                $entityManager->flush();
                $this->addFlash("success", "Décaissement modifié avec succès :)");
                return $this->redirectToRoute('app_logescom_sorties_decaissement_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');

                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/sorties/decaissement/edit.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'decaissement' => $decaissement,
                        'referer' => $referer,
                    ]);
                }

                
            }
        }

        if ($request->get("id_client_search")){
            $client_find = $userRep->find($request->get("id_client_search"));
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }else{
            $client_find = $decaissement->getClient();
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }

        return $this->render('logescom/sorties/decaissement/edit.html.twig', [
            'decaissement' => $decaissement,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'client_find' => $client_find,
            'soldes_collaborateur' => $soldes_collaborateur
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_sorties_decaissement_delete', methods: ['POST'])]
    public function delete(Request $request, Decaissement $decaissement, EntityManagerInterface $entityManager, Filesystem $filesystem, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$decaissement->getId(), $request->request->get('_token'))) {
            $justificatif = $decaissement->getDocument();
            $pdfPath = $this->getParameter("dossier_decaissement") . '/' . $justificatif;
            // Si le chemin du justificatif existe, supprimez également le fichier
            if ($justificatif && $filesystem->exists($pdfPath)) {
                $filesystem->remove($pdfPath);
            }

            $delete_dec = new DeleteDecaissement();
            $delete_dec->setReference($decaissement->getReference())
                    ->setMontant($decaissement->getMontant())
                    ->setNumeroChequeBord($decaissement->getNumeroChequeBord())
                    ->setBanqueCheque($decaissement->getBanqueCheque())
                    ->setDateSaisie(new \DateTime("now"))
                    ->setClient($decaissement->getClient()->getNom().' '.$decaissement->getClient()->getPrenom())
                    ->setTraitePar($decaissement->getTraitePar()->getPrenom().' '.$decaissement->getTraitePar()->getNom())
                    ->setDevise($decaissement->getDevise()->getNomDevise())
                    ->setCaisse($decaissement->getCompteDecaisser()->getDesignation())
                    ->setDateDecaissement($decaissement->getDateDecaissement())
                    ->setCommentaire("suppression décaissement");
            $entityManager->persist($delete_dec);
            $entityManager->remove($decaissement);
            $entityManager->flush();

            $this->addFlash("success", "Décaissement supprimé avec succès :)");
        }

        return $this->redirectToRoute('app_logescom_sorties_decaissement_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/pdf/reçu/{id}/{lieu_vente}', name: 'app_logescom_sorties_decaissement_recu_pdf', methods: ['GET'])]
    public function recuPdf(Decaissement $decaissement, LieuxVentes $lieu_vente, MouvementCollaborateurRepository $mouvementCollabRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep)
    {
        $entreprise = $entrepriseRep->findOneBy(['id' => 1]);
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$entreprise->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $soleCollaborateur = $mouvementCollabRep->findSoldeCollaborateur($decaissement->getClient());

        $collaborateur = $decaissement->getClient();
        $dateOp = $decaissement->getDateDecaissement();

        $ancienSoleCollaborateur = $mouvementCollabRep->findAncienSoldeCollaborateur($collaborateur, $dateOp);

        $html = $this->renderView('logescom/sorties/decaissement/recu_pdf.html.twig', [
            'decaissement' => $decaissement,
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
            'Content-Disposition' => 'inline; filename="réçu_decaissement.pdf"',
        ]);
    }
}
