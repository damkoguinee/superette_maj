<?php

namespace App\Controller\Logescom\Caisse;

use App\Entity\LieuxVentes;
use App\Entity\TransfertFond;
use App\Entity\MouvementCaisse;
use App\Form\TransfertFondType;
use App\Repository\CaisseRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransfertFondRepository;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TransfertProductsRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Repository\ModePaiementRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/caisse/transfert/fond')]
class TransfertFondController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_caisse_transfert_fond_index', methods: ['GET'])]
    public function index(TransfertFondRepository $transfertFondRepository, Request $request, CaisseRepository $caisseRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
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
            $transferts = $transfertFondRepository->findTransfertByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 25);
        }else{
            $transferts = $transfertFondRepository->findTransfertByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 25);
        }

        return $this->render('logescom/caisse/transfert_fond/index.html.twig', [
            'transfert_fonds' => $transferts,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
            'liste_caisse' => $caisseRep->findCaisseByLieu($lieu_vente),
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_caisse_transfert_fond_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, TransfertFondRepository $transfertRep, MouvementCaisseRepository $mouvementRep, CompteOperationRepository $compteOpRep, ModePaiementRepository $modePaieRep, CategorieOperationRepository $catetgorieOpRep, EntrepriseRepository $entrepriseRep): Response
    {
        $transfertFond = new TransfertFond();
        $form = $this->createForm(TransfertFondType::class, $transfertFond, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9]/', '', $montantString);
            $montant = floatval($montantString);
            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('ymd');
            $idSuivant =($transfertRep->findMaxId() + 1);
            $reference = "trans".$referenceDate . sprintf('%04d', $idSuivant);

            $caisse_depart = $form->getViewData()->getCaisseDepart();
            $caisse_recep = $form->getViewData()->getCaisseReception();
            $devise = $form->getViewData()->getDevise();
            

            if (empty($caisse_depart) and empty($caisse_recep)) {
                $this->addFlash("warning", "vous devez saisir au moins une caisse");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/caisse/transfert_fond/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'transfert_fond' => $transfertFond,
                        'referer' => $referer,
                    ]);
                }
            }else{
                $solde_caisse = $caisse_depart ? $mouvementRep->findSoldeCaisse($caisse_depart, $devise) : 1000000000000000000000000000000;
                if ($solde_caisse >= $montant) {                    
                    $transfertFond->setLieuVente($lieu_vente)
                            ->setEnvoyePar($this->getUser())
                            ->setReference($reference)
                            ->setMontant($montant)
                            ->setEtat("envoyer")
                            ->setType('transfert')
                            ->setDateSaisie(new \DateTime("now"));
    
                    $fichier = $form->get("document")->getData();
                    if ($fichier) {
                        $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                        $slugger = new AsciiSlugger();
                        $nouveauNomFichier = $slugger->slug($nomFichier);
                        $nouveauNomFichier .="_".uniqid();
                        $nouveauNomFichier .= "." .$fichier->guessExtension();
                        $fichier->move($this->getParameter("dossier_caisse"),$nouveauNomFichier);
                        $transfertFond->setDocument($nouveauNomFichier);
                    }
                    if (!empty($caisse_depart)) {
                        $mouvement_caisse_depart = new MouvementCaisse();
                        $categorie_op = $catetgorieOpRep->find(3);
                        $compte_op = $compteOpRep->find(1);
                        $mouvement_caisse_depart->setCategorieOperation($categorie_op)
                                ->setCompteOperation($compte_op)
                                ->setTypeMouvement("transfert")
                                ->setMontant(-$montant)
                                ->setSaisiePar($this->getUser())
                                ->setModePaie($modePaieRep->find(1))
                                ->setDevise($form->getViewData()->getDevise())
                                ->setCaisse($form->getViewData()->getCaisseDepart())
                                ->setLieuVente($lieu_vente)
                                ->setDateOperation($form->getViewData()->getDateOperation())
                                ->setDateSaisie(new \DateTime("now"));
                        $transfertFond->addMouvementCaiss($mouvement_caisse_depart);
                    }

                    if (!empty($caisse_recep)) {
                        if ($caisse_recep->gettype() != 'banque' ) {
                            $lieu_vente_recep = $caisse_recep->getPointDeVente()->getLieuVente();   

                            if ($caisse_recep->getPointDeVente()->getLieuVente() == $lieu_vente) {
                                $transfertFond->setTraitePar($this->getUser())
                                        ->setEtat("traité")
                                        ->setLieuVenteReception($lieu_vente_recep);
        
                                $mouvement_caisse_recep = new MouvementCaisse();
                                $categorie_op = $catetgorieOpRep->find(4);
                                $compte_op = $compteOpRep->find(4);
                                $mouvement_caisse_recep->setCategorieOperation($categorie_op)
                                        ->setCompteOperation($compte_op)
                                        ->setTypeMouvement("transfert")
                                        ->setSaisiePar($this->getUser())
                                        ->setMontant($montant)
                                        ->setModePaie($modePaieRep->find(1))
                                        ->setDevise($form->getViewData()->getDevise())
                                        ->setCaisse($form->getViewData()->getCaisseReception())
                                        ->setLieuVente($lieu_vente)
                                        ->setDateOperation($form->getViewData()->getDateOperation())
                                        ->setDateSaisie(new \DateTime("now"));
                                $transfertFond->addMouvementCaiss($mouvement_caisse_recep);
        
                            }else{
                                $transfertFond->setCaisseReception(null)
                                            ->setLieuVenteReception($lieu_vente_recep);
                            }
                        }else{
                            $transfertFond->setTraitePar($this->getUser())
                                        ->setEtat("traité")
                                        ->setType("banque");
        
                            $mouvement_caisse_recep = new MouvementCaisse();
                            $categorie_op = $catetgorieOpRep->find(4);
                            $compte_op = $compteOpRep->find(4);
                            $mouvement_caisse_recep->setCategorieOperation($categorie_op)
                                    ->setCompteOperation($compte_op)
                                    ->setTypeMouvement("transfert")
                                    ->setMontant($montant)
                                    ->setSaisiePar($this->getUser())
                                    ->setModePaie($modePaieRep->find(1))
                                    ->setDevise($form->getViewData()->getDevise())
                                    ->setCaisse($form->getViewData()->getCaisseReception())
                                    ->setLieuVente($lieu_vente)
                                    ->setDateOperation($form->getViewData()->getDateOperation())
                                    ->setDateSaisie(new \DateTime("now"));
                            $transfertFond->addMouvementCaiss($mouvement_caisse_recep);
                        }
                    }

                    if (empty($caisse_depart) or empty($caisse_recep)) {
                        $transfertFond->setType('autres')
                                    ->setTraitePar($this->getUser())
                                    ->setEtat("traité");
                    }
    
                    $entityManager->persist($transfertFond);
                    $entityManager->flush();
    
                    $this->addFlash("success", "transfert enregistré avec succès :)");
                    return $this->redirectToRoute('app_logescom_caisse_transfert_fond_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
                }else{
                    $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                    // Récupérer l'URL de la page précédente
                    $referer = $request->headers->get('referer');
                    if ($referer) {
                        $formView = $form->createView();
                        return $this->render('logescom/caisse/transfert_fond/new.html.twig', [
                            'entreprise' => $entrepriseRep->find(1),
                            'lieu_vente' => $lieu_vente,
                            'form' => $formView,
                            'transfert_fond' => $transfertFond,
                            'referer' => $referer,
                        ]);
                    }
                }
            }


            return $this->redirectToRoute('app_logescom_caisse_transfert_fond_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/caisse/transfert_fond/new.html.twig', [
            'transfert_fond' => $transfertFond,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_caisse_transfert_fond_show', methods: ['GET'])]
    public function show(TransfertFond $transfertFond, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/caisse/transfert_fond/show.html.twig', [
            'transfert_fond' => $transfertFond,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_caisse_transfert_fond_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TransfertFond $transfertFond, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep, ModePaiementRepository $modePaieRep, CompteOperationRepository $compteOpRep, CategorieOperationRepository $catetgorieOpRep, TransfertFondRepository $transfertRep, MouvementCaisseRepository $mouvementRep): Response
    {
        $form = $this->createForm(TransfertFondType::class, $transfertFond, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9]/', '', $montantString);
            $montant = floatval($montantString);
            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('ymd');
            $idSuivant =($transfertRep->findMaxId() + 1);
            $reference = "trans".$referenceDate . sprintf('%04d', $idSuivant);

            $caisse_depart = $form->getViewData()->getCaisseDepart();
            $caisse_recep = $form->getViewData()->getCaisseReception();
            $devise = $form->getViewData()->getDevise();

            if (empty($caisse_depart) and empty($caisse_recep)) {
                $this->addFlash("warning", "vous devez saisir au moins une caisse");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/caisse/transfert_fond/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'transfert_fond' => $transfertFond,
                        'referer' => $referer,
                    ]);
                }
            }else{
                $mouvements = $mouvementRep->findBy(['transfertFond' => $transfertFond]); 

                foreach ($mouvements as $mouvement) {
                    $entityManager->remove($mouvement);
                }

                $solde_caisse = $caisse_depart ? $mouvementRep->findSoldeCaisse($caisse_depart, $devise) : 1000000000000000000000000000000;

                if ($solde_caisse >= $montant) {

                    $transfertFond->setLieuVente($lieu_vente)
                            ->setEnvoyePar($this->getUser())
                            ->setMontant($montant)
                            ->setDateSaisie(new \DateTime("now"));
    
                            $justificatif =$form->get("document")->getData();
                            if ($justificatif) {
                                if ($transfertFond->getDocument()) {
                                    $ancienJustificatif=$this->getParameter("dossier_caisse")."/".$transfertFond->getDocument();
                                    if (file_exists($ancienJustificatif)) {
                                        unlink($ancienJustificatif);
                                    }
                                }
                                $nomJustificatif= pathinfo($justificatif->getClientOriginalName(), PATHINFO_FILENAME);
                                $slugger = new AsciiSlugger();
                                $nouveauNomJustificatif = $slugger->slug($nomJustificatif);
                                $nouveauNomJustificatif .="_".uniqid();
                                $nouveauNomJustificatif .= "." .$justificatif->guessExtension();
                                $justificatif->move($this->getParameter("dossier_caisse"),$nouveauNomJustificatif);
                                $transfertFond->setDocument($nouveauNomJustificatif);
            
                            }
                    if (!empty($caisse_depart)) {
                        $mouvement_caisse_depart = new MouvementCaisse();
                        $categorie_op = $catetgorieOpRep->find(4);
                        $compte_op = $compteOpRep->find(4);
                        $mouvement_caisse_depart->setCategorieOperation($categorie_op)
                                ->setCompteOperation($compte_op)
                                ->setMontant(-$montant)
                                ->setSaisiePar($this->getUser())
                                ->setDevise($form->getViewData()->getDevise())
                                ->setModePaie($modePaieRep->find(1))
                                ->setCaisse($form->getViewData()->getCaisseDepart())
                                ->setTypeMouvement('transfert')
                                ->setLieuVente($lieu_vente)
                                ->setDateOperation($form->getViewData()->getDateOperation())
                                ->setDateSaisie(new \DateTime("now"));
                        $transfertFond->addMouvementCaiss($mouvement_caisse_depart);
                    }

                    if (!empty($caisse_recep)) {
    
                        if ($caisse_recep->gettype() != 'banque' ) {
                            if ($caisse_recep->getPointDeVente()->getLieuVente() == $lieu_vente) {
                                $transfertFond->setTraitePar($this->getUser())
                                        ->setEtat("traité");
        
                                $mouvement_caisse_recep = new MouvementCaisse();
                                $categorie_op = $catetgorieOpRep->find(4);
                                $compte_op = $compteOpRep->find(4);
                                $mouvement_caisse_recep->setCategorieOperation($categorie_op)
                                        ->setCompteOperation($compte_op)
                                        ->setSaisiePar($this->getUser())
                                        ->setMontant($montant)
                                        ->setModePaie($modePaieRep->find(1))
                                        ->setDevise($form->getViewData()->getDevise())
                                        ->setCaisse($form->getViewData()->getCaisseReception())
                                        ->setLieuVente($lieu_vente)
                                        ->setTypeMouvement('transfert')
                                        ->setDateOperation($form->getViewData()->getDateOperation())
                                        ->setDateSaisie(new \DateTime("now"));
                                $transfertFond->addMouvementCaiss($mouvement_caisse_recep);
        
                            }else{
                                $transfertFond->setCaisseReception(null);
                            }
                        }else{
                            $transfertFond->setTraitePar($this->getUser())
                                        ->setEtat("traité")
                                        ->setType("banque");
        
                            $mouvement_caisse_recep = new MouvementCaisse();
                            $categorie_op = $catetgorieOpRep->find(4);
                            $compte_op = $compteOpRep->find(4);
                            $mouvement_caisse_recep->setCategorieOperation($categorie_op)
                                    ->setCompteOperation($compte_op)
                                    ->setMontant($montant)
                                    ->setSaisiePar($this->getUser())
                                    ->setModePaie($modePaieRep->find(1))
                                    ->setDevise($form->getViewData()->getDevise())
                                    ->setCaisse($form->getViewData()->getCaisseReception())
                                    ->setTypeMouvement('transfert')
                                    ->setLieuVente($lieu_vente)
                                    ->setDateOperation($form->getViewData()->getDateOperation())
                                    ->setDateSaisie(new \DateTime("now"));
                            $transfertFond->addMouvementCaiss($mouvement_caisse_recep);
                        }
                    }
    
                    $entityManager->persist($transfertFond);
                    $entityManager->flush();
    
                    $this->addFlash("success", "transfert modifié avec succès :)");
                    return $this->redirectToRoute('app_logescom_caisse_transfert_fond_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
                }else{
                    $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                    // Récupérer l'URL de la page précédente
                    $referer = $request->headers->get('referer');
                    if ($referer) {
                        $formView = $form->createView();
                        return $this->render('logescom/caisse/transfert_fond/new.html.twig', [
                            'entreprise' => $entrepriseRep->find(1),
                            'lieu_vente' => $lieu_vente,
                            'form' => $formView,
                            'transfert_fond' => $transfertFond,
                            'referer' => $referer,
                        ]);
                    }
                }
            }


            return $this->redirectToRoute('app_logescom_caisse_transfert_fond_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/caisse/transfert_fond/edit.html.twig', [
            'transfert_fond' => $transfertFond,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_caisse_transfert_fond_delete', methods: ['POST'])]
    public function delete(Request $request, TransfertFond $transfertFond, EntityManagerInterface $entityManager, Filesystem $filesystem, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transfertFond->getId(), $request->request->get('_token'))) {

            $justificatif = $transfertFond->getDocument();
            $pdfPath = $this->getParameter("dossier_devise") . '/' . $justificatif;
            // Si le chemin du justificatif existe, supprimez également le fichier
            if ($justificatif && $filesystem->exists($pdfPath)) {
                $filesystem->remove($pdfPath);
            }


            $entityManager->remove($transfertFond);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_caisse_transfert_fond_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete/reception/{id}/{lieu_vente}', name: 'app_logescom_caisse_transfert_fond_delete_reception', methods: ['POST'])]
    public function deleteReception(Request $request, MouvementCaisse $mouvement, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mouvement->getId(), $request->request->get('_token'))) {
            $transfertFond = $mouvement->getTransfertFond();
            $transfertFond->setCaisseReception(null)
                    ->setTraitePar($this->getUser())
                    ->setEtat("envoyer");
            $entityManager->persist($transfertFond);
            $entityManager->remove($mouvement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_caisse_transfert_fond_reception', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('/reception/{lieu_vente}', name: 'app_logescom_caisse_transfert_fond_reception', methods: ['GET'])]
    public function receptionTransfert(TransfertFondRepository $transfertFondRepository, Request $request, CaisseRepository $caisseRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep, CompteOperationRepository $compteOpRep, MouvementCaisseRepository $mouvementRep, CategorieOperationRepository $catetgorieOpRep, ModePaiementRepository $modePaieRep, EntityManagerInterface $em): Response
    {
        if ($request->get('caisse_recep')) {
            $caisse_recep = $caisseRep->find($request->get('caisse_recep'));
            $transfertFond = $transfertFondRepository->find($request->get('id_transfert'));
            $transfertFond->setCaisseReception($caisse_recep)
                    ->setTraitePar($this->getUser())
                    ->setEtat("traité")
                    ->setDateReception(new \DateTime("now"));

            $mouvement_caisse_recep = new MouvementCaisse();
            $categorie_op = $catetgorieOpRep->find(4);
            $compte_op = $compteOpRep->find(4);
            $mouvement_caisse_recep->setCategorieOperation($categorie_op)
                    ->setCompteOperation($compte_op)
                    ->setTypeMouvement("transfert")
                    ->setMontant($transfertFond->getMontant())
                    ->setDevise($transfertFond->getDevise())
                    ->setCaisse($caisse_recep)
                    ->setLieuVente($lieu_vente)
                    ->setModePaie($modePaieRep->find(1))
                    ->setSaisiePar($this->getUser())
                    ->setDateOperation($transfertFond->getDateOperation())
                    ->setDateSaisie(new \DateTime("now"))
                    ->setTransfertFond($transfertFond);
            $em->persist($transfertFond);
            $em->persist($mouvement_caisse_recep);
            $em->flush();
            $this->addFlash("success", 'Transfert réceptionné avec succès :) ');
            return new RedirectResponse($this->generateUrl('app_logescom_caisse_transfert_fond_reception', ['lieu_vente' => $lieu_vente->getId()]));
        }
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
        $transferts = $transfertFondRepository->findReceptionTransfertByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 15);

        if ($request->get("search")){
            $transferts_recep = $mouvementRep->findReceptionTransfertByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 15);
        }else{
            $transferts_recep = $mouvementRep->findReceptionTransfertByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 15);
        }

        return $this->render('logescom/caisse/transfert_fond/reception.html.twig', [
            'transfert_fonds' => $transferts,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
            'liste_caisse' => $caisseRep->findCaisseByLieu($lieu_vente),
            'transferts_recep' => $transferts_recep
        ]);
    }
}
