<?php

namespace App\Controller\Logescom\Caisse;

use App\Entity\LieuxVentes;
use App\Entity\EchangeDevise;
use App\Entity\MouvementCaisse;
use App\Form\EchangeDeviseType;
use App\Repository\CaisseRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EchangeDeviseRepository;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Repository\ModePaiementRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/caisse/echange/devise')]
class EchangeDeviseController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_caisse_echange_devise_index', methods: ['GET'])]
    public function index(EchangeDeviseRepository $EchangeDeviseRepository, Request $request, CaisseRepository $caisseRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("search")){
            $search = $caisseRep->find($request->get("search"));
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
        if ($request->get("search")){
            $echanges = $EchangeDeviseRepository->findTransfertByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 25);
        }else{
            $echanges = $EchangeDeviseRepository->findTransfertByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 25);
        }

        return $this->render('logescom/caisse/echange_devise/index.html.twig', [
            'echange_devises' => $echanges,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
            'liste_caisse' => $caisseRep->findCaisseByLieu($lieu_vente),
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_caisse_echange_devise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EchangeDeviseRepository $echangeDeviseRep, MouvementCaisseRepository $mouvementRep, CompteOperationRepository $compteOpRep, ModePaiementRepository $modePaieRep, CategorieOperationRepository $catetgorieOpRep, EntrepriseRepository $entrepriseRep): Response
    {
        $echangeDevise = new EchangeDevise();
        $form = $this->createForm(EchangeDeviseType::class, $echangeDevise, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantOrigine = floatval(preg_replace('/[^0-9,.]/', '', $form->get('montantOrigine')->getData()));
            $taux = floatval(preg_replace('/[^0-9,.]/', '', $form->get('taux')->getData()));

            $montantDestination = $taux ? ($montantOrigine / $taux ) : 1;


            $dateDuJour = new \DateTime();
            // $referenceDate = $dateDuJour->format('ymd');
            // $idSuivant =($echangeDeviseRep->findMaxId() + 1);
            // $reference = "ech".$referenceDate . sprintf('%04d', $idSuivant);                

            $caisseOrigine = $form->getViewData()->getCaisseOrigine();
            $caisseDestination= $form->getViewData()->getCaisseDestination();
            $deviseOrigine = $form->getViewData()->getDeviseOrigine();
            $deviseDestination = $form->getViewData()->getDeviseDestination();

            $solde_caisse = $caisseOrigine ? $mouvementRep->findSoldeCaisse($caisseOrigine, $deviseOrigine) : 1000000000000000000000000000000;
            
            if ($solde_caisse >= $montantOrigine) {  
                $echangeDevise->setLieuVente($lieu_vente)
                        ->setMontantOrigine($montantOrigine)
                        ->setMontantDestination($montantDestination)
                        ->setTaux($taux)
                        ->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

                $fichier = $form->get("document")->getData();
                if ($fichier) {
                    $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomFichier = $slugger->slug($nomFichier);
                    $nouveauNomFichier .="_".uniqid();
                    $nouveauNomFichier .= "." .$fichier->guessExtension();
                    $fichier->move($this->getParameter("dossier_devise"),$nouveauNomFichier);
                    $echangeDevise->setDocument($nouveauNomFichier);
                }                 

                if (!empty($caisseOrigine)) {
                    $mouvement_caisse_depart = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(7);
                    $compte_op = $compteOpRep->find(7);
                    $mouvement_caisse_depart->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("echange")
                            ->setSaisiePar($this->getUser())
                            ->setMontant(-$montantOrigine)
                            ->setModePaie($modePaieRep->find(1))
                            ->setTaux($taux)
                            ->setDevise($form->getViewData()->getDeviseOrigine())
                            ->setCaisse($form->getViewData()->getCaisseOrigine())
                            ->setLieuVente($lieu_vente)
                            ->setDateOperation($form->getViewData()->getDateEchange())
                            ->setDateSaisie(new \DateTime("now"));
                    $echangeDevise->addMouvementCaiss($mouvement_caisse_depart);
                }

                if (!empty($caisseDestination)) {
                    $mouvement_caisse_recep = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(7);
                    $compte_op = $compteOpRep->find(7);
                    $mouvement_caisse_recep->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("echange")
                            ->setSaisiePar($this->getUser())
                            ->setMontant($montantDestination)
                            ->setModePaie($modePaieRep->find(1))
                            ->setTaux($taux)
                            ->setDevise($form->getViewData()->getDeviseDestination())
                            ->setCaisse($form->getViewData()->getCaisseDestination())
                            ->setLieuVente($lieu_vente)
                            ->setDateOperation($form->getViewData()->getDateEchange())
                            ->setDateSaisie(new \DateTime("now"));
                    $echangeDevise->addMouvementCaiss($mouvement_caisse_recep);
                }

                $entityManager->persist($echangeDevise);
                $entityManager->flush();

                $this->addFlash("success", "Echange enregistré avec succès :)");
                return $this->redirectToRoute('app_logescom_caisse_echange_devise_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/caisse/echange_devise/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'echange_devise' => $echangeDevise,
                        'referer' => $referer,
                    ]);
                }
            }
            return $this->redirectToRoute('app_logescom_caisse_echange_devise_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/caisse/echange_devise/new.html.twig', [
            'echange_devise' => $echangeDevise,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/new/vente/{lieu_vente}', name: 'app_logescom_caisse_echange_devise_new_vente', methods: ['GET', 'POST'])]
    public function venteDevise(Request $request, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EchangeDeviseRepository $echangeDeviseRep, MouvementCaisseRepository $mouvementRep, CompteOperationRepository $compteOpRep, ModePaiementRepository $modePaieRep, CategorieOperationRepository $catetgorieOpRep, EntrepriseRepository $entrepriseRep): Response
    {
        $echangeDevise = new EchangeDevise();
        $form = $this->createForm(EchangeDeviseType::class, $echangeDevise, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantOrigine = floatval(preg_replace('/[^0-9,.]/', '', $form->get('montantOrigine')->getData()));
            $taux = floatval(preg_replace('/[^0-9,.]/', '', $form->get('taux')->getData()));

            $montantDestination = $taux ? ($montantOrigine * $taux ) : 1;

            $dateDuJour = new \DateTime();
            // $referenceDate = $dateDuJour->format('ymd');
            // $idSuivant =($echangeDeviseRep->findMaxId() + 1);
            // $reference = "ech".$referenceDate . sprintf('%04d', $idSuivant);                

            $caisseOrigine = $form->getViewData()->getCaisseOrigine();
            $caisseDestination= $form->getViewData()->getCaisseDestination();
            $deviseOrigine = $form->getViewData()->getDeviseOrigine();
            $deviseDestination = $form->getViewData()->getDeviseDestination();

            $solde_caisse = $caisseOrigine ? $mouvementRep->findSoldeCaisse($caisseOrigine, $deviseOrigine) : 1000000000000000000000000000000;
            
            if ($solde_caisse >= $montantOrigine) {  
                $echangeDevise->setLieuVente($lieu_vente)
                        ->setMontantOrigine($montantOrigine)
                        ->setMontantDestination($montantDestination)
                        ->setTaux($taux)
                        ->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

                $fichier = $form->get("document")->getData();
                if ($fichier) {
                    $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomFichier = $slugger->slug($nomFichier);
                    $nouveauNomFichier .="_".uniqid();
                    $nouveauNomFichier .= "." .$fichier->guessExtension();
                    $fichier->move($this->getParameter("dossier_devise"),$nouveauNomFichier);
                    $echangeDevise->setDocument($nouveauNomFichier);
                }                 

                if (!empty($caisseOrigine)) {
                    $mouvement_caisse_depart = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(7);
                    $compte_op = $compteOpRep->find(7);
                    $mouvement_caisse_depart->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("echange")
                            ->setSaisiePar($this->getUser())
                            ->setMontant(-$montantOrigine)
                            ->setModePaie($modePaieRep->find(1))
                            ->setTaux($taux)
                            ->setDevise($form->getViewData()->getDeviseOrigine())
                            ->setCaisse($form->getViewData()->getCaisseOrigine())
                            ->setLieuVente($lieu_vente)
                            ->setDateOperation($form->getViewData()->getDateEchange())
                            ->setDateSaisie(new \DateTime("now"));
                    $echangeDevise->addMouvementCaiss($mouvement_caisse_depart);
                }

                if (!empty($caisseDestination)) {
                    $mouvement_caisse_recep = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(7);
                    $compte_op = $compteOpRep->find(7);
                    $mouvement_caisse_recep->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("echange")
                            ->setSaisiePar($this->getUser())
                            ->setMontant($montantDestination)
                            ->setModePaie($modePaieRep->find(1))
                            ->setTaux($taux)
                            ->setDevise($form->getViewData()->getDeviseDestination())
                            ->setCaisse($form->getViewData()->getCaisseDestination())
                            ->setLieuVente($lieu_vente)
                            ->setDateOperation($form->getViewData()->getDateEchange())
                            ->setDateSaisie(new \DateTime("now"));
                    $echangeDevise->addMouvementCaiss($mouvement_caisse_recep);
                }

                $entityManager->persist($echangeDevise);
                $entityManager->flush();

                $this->addFlash("success", "Echange enregistré avec succès :)");
                return $this->redirectToRoute('app_logescom_caisse_echange_devise_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/caisse/echange_devise/vente_devise.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'echange_devise' => $echangeDevise,
                        'referer' => $referer,
                    ]);
                }
            }
            return $this->redirectToRoute('app_logescom_caisse_echange_devise_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/caisse/echange_devise/vente_devise.html.twig', [
            'echange_devise' => $echangeDevise,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_caisse_echange_devise_show', methods: ['GET'])]
    public function show(EchangeDevise $echangeDevise, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        
        return $this->render('logescom/caisse/echange_devise/show.html.twig', [
            'echange_devise' => $echangeDevise,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_caisse_echange_devise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EchangeDevise $echangeDevise, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep, MouvementCaisseRepository $mouvementCaisseRep, EchangeDeviseRepository $echnageDeviseRep, CompteOperationRepository $compteOpRep, CategorieOperationRepository $catetgorieOpRep, MouvementCaisseRepository $mouvementRep): Response
    {
        $form = $this->createForm(EchangeDeviseType::class, $echangeDevise, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantOrigine = floatval(preg_replace('/[^0-9,.]/', '', $form->get('montantOrigine')->getData()));
            $taux = floatval(preg_replace('/[^0-9,.]/', '', $form->get('taux')->getData()));

            $montantDestination = $taux ? ($montantOrigine / $taux ) : 1;


            $dateDuJour = new \DateTime();
            // $referenceDate = $dateDuJour->format('ymd');
            // $idSuivant =($echangeDeviseRep->findMaxId() + 1);
            // $reference = "ech".$referenceDate . sprintf('%04d', $idSuivant);                

            $caisseOrigine = $form->getViewData()->getCaisseOrigine();
            $caisseDestination= $form->getViewData()->getCaisseDestination();
            $deviseOrigine = $form->getViewData()->getDeviseOrigine();
            $deviseDestination = $form->getViewData()->getDeviseDestination();

            
            $solde_caisse = $caisseOrigine ? $mouvementRep->findSoldeCaisse($caisseOrigine, $deviseOrigine) : 1000000000000000000000000000000;
            
            if ($solde_caisse >= $montantOrigine) {  

                $echangeDevise->setLieuVente($lieu_vente)
                        ->setMontantOrigine($montantOrigine)
                        ->setMontantDestination($montantDestination)
                        ->setTaux($taux)
                        ->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));
    
                $justificatif =$form->get("document")->getData();
                if ($justificatif) {
                    if ($echangeDevise->getDocument()) {
                        $ancienJustificatif=$this->getParameter("dossier_caisse")."/".$echangeDevise->getDocument();
                        if (file_exists($ancienJustificatif)) {
                            unlink($ancienJustificatif);
                        }
                    }
                    $nomJustificatif= pathinfo($justificatif->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomJustificatif = $slugger->slug($nomJustificatif);
                    $nouveauNomJustificatif .="_".uniqid();
                    $nouveauNomJustificatif .= "." .$justificatif->guessExtension();
                    $justificatif->move($this->getParameter("dossier_devise"),$nouveauNomJustificatif);
                    $echangeDevise->setDocument($nouveauNomJustificatif);

                }
                $mouvements = $mouvementCaisseRep->findBy(['echange' => $echangeDevise]);
                foreach ($mouvements as $mouvement) {
                    $entityManager->remove($mouvement);
                }

                if (!empty($caisseOrigine)) {
                    $mouvement_caisse_depart = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(7);
                    $compte_op = $compteOpRep->find(7);
                    $mouvement_caisse_depart->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("echange")
                            ->setSaisiePar($this->getUser())
                            ->setMontant(-$montantOrigine)
                            ->setTaux($taux)
                            ->setDevise($form->getViewData()->getDeviseOrigine())
                            ->setCaisse($form->getViewData()->getCaisseOrigine())
                            ->setLieuVente($lieu_vente)
                            ->setDateOperation($form->getViewData()->getDateEchange())
                            ->setDateSaisie(new \DateTime("now"));
                    $echangeDevise->addMouvementCaiss($mouvement_caisse_depart);
                }

                if (!empty($caisseDestination)) {
                    $mouvement_caisse_recep = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(7);
                    $compte_op = $compteOpRep->find(7);
                    $mouvement_caisse_recep->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("echange")
                            ->setMontant($montantDestination)
                            ->setTaux($taux)
                            ->setSaisiePar($this->getUser())
                            ->setDevise($form->getViewData()->getDeviseDestination())
                            ->setCaisse($form->getViewData()->getCaisseDestination())
                            ->setLieuVente($lieu_vente)
                            ->setDateOperation($form->getViewData()->getDateEchange())
                            ->setDateSaisie(new \DateTime("now"));
                    $echangeDevise->addMouvementCaiss($mouvement_caisse_recep);
                }

                $entityManager->persist($echangeDevise);
                $entityManager->flush();

                $this->addFlash("success", "echange devise modifié avec succès :)");
                return $this->redirectToRoute('app_logescom_caisse_echange_devise_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/caisse/echange_devise/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'echange_devise' => $echangeDevise,
                        'referer' => $referer,
                    ]);
                }
            }


            return $this->redirectToRoute('app_logescom_caisse_echange_devise_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/caisse/echange_devise/edit.html.twig', [
            'echange_devise' => $echangeDevise,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/edit/vente/{id}/{lieu_vente}', name: 'app_logescom_caisse_echange_devise_edit_vente', methods: ['GET', 'POST'])]
    public function editVenteDevise(Request $request, EchangeDevise $echangeDevise, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep, MouvementCaisseRepository $mouvementCaisseRep, EchangeDeviseRepository $echnageDeviseRep, CompteOperationRepository $compteOpRep, CategorieOperationRepository $catetgorieOpRep, MouvementCaisseRepository $mouvementRep): Response
    {
        $form = $this->createForm(EchangeDeviseType::class, $echangeDevise, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantOrigine = floatval(preg_replace('/[^0-9,.]/', '', $form->get('montantOrigine')->getData()));
            $taux = floatval(preg_replace('/[^0-9,.]/', '', $form->get('taux')->getData()));

            $montantDestination = $taux ? ($montantOrigine * $taux ) : 1;


            $dateDuJour = new \DateTime();
            // $referenceDate = $dateDuJour->format('ymd');
            // $idSuivant =($echangeDeviseRep->findMaxId() + 1);
            // $reference = "ech".$referenceDate . sprintf('%04d', $idSuivant);                

            $caisseOrigine = $form->getViewData()->getCaisseOrigine();
            $caisseDestination= $form->getViewData()->getCaisseDestination();
            $deviseOrigine = $form->getViewData()->getDeviseOrigine();
            $deviseDestination = $form->getViewData()->getDeviseDestination();

            
            $solde_caisse = $caisseOrigine ? $mouvementRep->findSoldeCaisse($caisseOrigine, $deviseOrigine) : 1000000000000000000000000000000;
            
            if ($solde_caisse >= $montantOrigine) {  

                $echangeDevise->setLieuVente($lieu_vente)
                        ->setMontantOrigine($montantOrigine)
                        ->setMontantDestination($montantDestination)
                        ->setTaux($taux)
                        ->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));
    
                $justificatif =$form->get("document")->getData();
                if ($justificatif) {
                    if ($echangeDevise->getDocument()) {
                        $ancienJustificatif=$this->getParameter("dossier_caisse")."/".$echangeDevise->getDocument();
                        if (file_exists($ancienJustificatif)) {
                            unlink($ancienJustificatif);
                        }
                    }
                    $nomJustificatif= pathinfo($justificatif->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomJustificatif = $slugger->slug($nomJustificatif);
                    $nouveauNomJustificatif .="_".uniqid();
                    $nouveauNomJustificatif .= "." .$justificatif->guessExtension();
                    $justificatif->move($this->getParameter("dossier_devise"),$nouveauNomJustificatif);
                    $echangeDevise->setDocument($nouveauNomJustificatif);

                }
                $mouvements = $mouvementCaisseRep->findBy(['echange' => $echangeDevise]);
                foreach ($mouvements as $mouvement) {
                    $entityManager->remove($mouvement);
                }

                if (!empty($caisseOrigine)) {
                    $mouvement_caisse_depart = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(7);
                    $compte_op = $compteOpRep->find(7);
                    $mouvement_caisse_depart->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("echange")
                            ->setSaisiePar($this->getUser())
                            ->setMontant(-$montantOrigine)
                            ->setTaux($taux)
                            ->setDevise($form->getViewData()->getDeviseOrigine())
                            ->setCaisse($form->getViewData()->getCaisseOrigine())
                            ->setLieuVente($lieu_vente)
                            ->setDateOperation($form->getViewData()->getDateEchange())
                            ->setDateSaisie(new \DateTime("now"));
                    $echangeDevise->addMouvementCaiss($mouvement_caisse_depart);
                }

                if (!empty($caisseDestination)) {
                    $mouvement_caisse_recep = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(7);
                    $compte_op = $compteOpRep->find(7);
                    $mouvement_caisse_recep->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("echange")
                            ->setMontant($montantDestination)
                            ->setTaux($taux)
                            ->setSaisiePar($this->getUser())
                            ->setDevise($form->getViewData()->getDeviseDestination())
                            ->setCaisse($form->getViewData()->getCaisseDestination())
                            ->setLieuVente($lieu_vente)
                            ->setDateOperation($form->getViewData()->getDateEchange())
                            ->setDateSaisie(new \DateTime("now"));
                    $echangeDevise->addMouvementCaiss($mouvement_caisse_recep);
                }

                $entityManager->persist($echangeDevise);
                $entityManager->flush();

                $this->addFlash("success", "echange devise modifié avec succès :)");
                return $this->redirectToRoute('app_logescom_caisse_echange_devise_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/caisse/echange_devise/vente_devise.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'echange_devise' => $echangeDevise,
                        'referer' => $referer,
                    ]);
                }
            }


            return $this->redirectToRoute('app_logescom_caisse_echange_devise_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('logescom/caisse/echange_devise/edit_vente.html.twig', [
            'echange_devise' => $echangeDevise,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_caisse_echange_devise_delete', methods: ['POST'])]
    public function delete(Request $request, EchangeDevise $echangeDevise, EntityManagerInterface $entityManager, Filesystem $filesystem, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$echangeDevise->getId(), $request->request->get('_token'))) {

            $justificatif = $echangeDevise->getDocument();
            $pdfPath = $this->getParameter("dossier_devise") . '/' . $justificatif;
            // Si le chemin du justificatif existe, supprimez également le fichier
            if ($justificatif && $filesystem->exists($pdfPath)) {
                $filesystem->remove($pdfPath);
            }

            $entityManager->remove($echangeDevise);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_caisse_echange_devise_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}
