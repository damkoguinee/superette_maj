<?php

namespace App\Controller\Logescom\Sorties;

use App\Entity\Depenses;
use App\Form\DepensesType;
use App\Entity\LieuxVentes;
use App\Entity\MouvementCaisse;
use App\Entity\ModifDecaissement;
use App\Entity\DeleteDecaissement;
use App\Entity\Modification;
use App\Repository\CategorieDepenseRepository;
use App\Repository\DeviseRepository;
use App\Repository\DepensesRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ComptesDepotRepository;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ServicesGenerauxRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Repository\ModifDecaissementRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/sorties/depenses')]
class DepensesController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_sorties_depenses_index', methods: ['GET'])]
    public function index(Request $request, DepensesRepository $depensesRepository, CategorieDepenseRepository $categorieDepenseRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("categorie")){
            $search = $categorieDepenseRep->find($request->get("categorie"));
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
        if ($request->get("categorie")){
            $depenses = $depensesRepository->findDepensesByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 25);

            $cumulDepenses = $depensesRepository->totalDepensesParPeriodeParLieuParCategorie($lieu_vente, $search, $date1, $date2);
        }else{
            $depenses = $depensesRepository->findDepensesByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 25);

            $cumulDepenses = $depensesRepository->totalDepensesParPeriodeParLieu($lieu_vente, $date1, $date2);

        }
        return $this->render('logescom/sorties/depenses/index.html.twig', [
            'depenses' => $depenses,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'categories' => $categorieDepenseRep->findBy([], ['description' => 'ASC']),
            'search' => $search,
            'cumulDepenses' => $cumulDepenses

        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_sorties_depenses_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DeviseRepository $deviseRep, CategorieOperationRepository $catOpRep, MouvementCaisseRepository $mouvCaisseRep, CompteOperationRepository $compteOpRep, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $depense = new Depenses();
        $form = $this->createForm(DepensesType::class, $depense, ['lieu_vente' => $lieu_vente] );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval($montantString);

            $montantString = $form->get('tva')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant_tva = floatval($montantString);

            $caisse = $form->getViewData()->getCaisseRetrait();
            $devise = $form->getViewData()->getDevise();
            $solde_caisse = $mouvCaisseRep->findSoldeCaisse($caisse, $devise);
            if ($solde_caisse >= $montant) {
                $depense->setTraitePar($this->getUser())
                        ->setMontant($montant)
                        ->setTva($montant_tva)
                        ->setLieuVente($lieu_vente)
                        ->setDateSaisie(new \DateTime("now"));
                $fichier = $form->get("justificatif")->getData();
                if ($fichier) {
                    $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomFichier = $slugger->slug($nomFichier);
                    $nouveauNomFichier .="_".uniqid();
                    $nouveauNomFichier .= "." .$fichier->guessExtension();
                    $fichier->move($this->getParameter("dossier_depenses"),$nouveauNomFichier);
                    $depense->setJustificatif($nouveauNomFichier);
                }
                $mouvement = new MouvementCaisse();
                $mouvement->setCategorieOperation($catOpRep->find(2))
                        ->setCompteOperation($compteOpRep->find(3))
                        ->setLieuVente($lieu_vente)
                        ->setTypeMouvement("depenses")
                        ->setSaisiePar($this->getUser())
                        ->setModePaie($form->getViewData()->getModePaiement())
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateOperation($form->getViewData()->getDateDepense())
                        ->setMontant((-1) * $montant)
                        ->setCaisse($form->getViewData()->getCaisseRetrait())
                        ->setDevise($form->getViewData()->getDevise());
                $depense->addMouvementCaiss($mouvement);

                $entityManager->persist($depense);
                $entityManager->flush();
                $this->addFlash("success", "dépense ajoutée avec succès :)");
                return $this->redirectToRoute('app_logescom_sorties_depenses_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/sorties/depenses/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'depense' => $depense,
                        'referer' => $referer,
                    ]);
                }
            }
            
        }

        return $this->render('logescom/sorties/depenses/new.html.twig', [
            'depense' => $depense,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_sorties_depenses_show', methods: ['GET'])]
    public function show(Depenses $depense, ModifDecaissementRepository $modifDecRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $depense_modif = $modifDecRep->findBy(['depense' => $depense]);
        return $this->render('logescom/sorties/depenses/show.html.twig', [
            'depense' => $depense,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'depenses_modif' =>  $depense_modif,

        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_sorties_depenses_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Depenses $depense, MouvementCaisseRepository $mouvCaisseRep, DepensesRepository $depensesRep, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $depense_init = $depensesRep->find($depense);
        $depense_modif = new Modification();
        $depense_modif->setMontant($depense_init->getMontant())
                        ->setCaisse($depense_init->getCaisseRetrait())
                        ->setOrigine("depense")
                        ->setDevise($depense_init->getDevise())
                        ->setTraitePar($depense_init->getTraitePar())
                        ->setDateOperation($depense_init->getDateDepense())
                        ->setDepense($depense_init);
        $entityManager->persist($depense_modif);

        $form = $this->createForm(DepensesType::class, $depense, ['lieu_vente' => $lieu_vente] );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval($montantString);

            $montantString = $form->get('tva')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant_tva = floatval($montantString);

            $caisse = $form->getViewData()->getCaisseRetrait();
            $devise = $form->getViewData()->getDevise();
            $solde_caisse = $mouvCaisseRep->findSoldeCaisse($caisse, $devise);
            if ($solde_caisse >= $montant) {
                $depense->setMontant($montant)
                        ->setTva($montant_tva)
                        ->setTraitePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

                $mouvement = $mouvCaisseRep->findOneBy(['depense' => $depense]);
                $mouvement->setDateSaisie(new \DateTime("now"))
                        ->setMontant($montant)
                        ->setSaisiePar($this->getUser())
                        ->setDateOperation($form->getViewData()->getDateDepense())
                        ->setCaisse($form->getViewData()->getCaisseRetrait())
                        ->setDevise($form->getViewData()->getDevise());

                $justificatif =$form->get("justificatif")->getData();
                if ($justificatif) {
                    if ($depense->getJustificatif()) {
                        $ancienJustificatif=$this->getParameter("dossier_depenses")."/".$depense->getJustificatif();
                        if (file_exists($ancienJustificatif)) {
                            unlink($ancienJustificatif);
                        }
                    }
                    $nomJustificatif= pathinfo($justificatif->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomJustificatif = $slugger->slug($nomJustificatif);
                    $nouveauNomJustificatif .="_".uniqid();
                    $nouveauNomJustificatif .= "." .$justificatif->guessExtension();
                    $justificatif->move($this->getParameter("dossier_depenses"),$nouveauNomJustificatif);
                    $depense->setJustificatif($nouveauNomJustificatif);

                }

                $entityManager->persist($depense);
                $entityManager->persist($depense_modif);
                $entityManager->flush();
                $this->addFlash("success", "Dépense enregistrée avec succès :)");
                return $this->redirectToRoute('app_logescom_sorties_depenses_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('logescom/sorties/depenses/edit.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'form' => $formView,
                        'depense' => $depense,
                        'referer' => $referer,
                    ]);
                }
            }

        }

        return $this->render('logescom/sorties/depenses/edit.html.twig', [
            'depense' => $depense,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_sorties_depenses_delete', methods: ['POST'])]
    public function delete(Request $request, Depenses $depense, Filesystem $filesystem, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $justificatif = $depense->getJustificatif();
        if ($this->isCsrfTokenValid('delete'.$depense->getId(), $request->request->get('_token'))) {
            $pdfPath = $this->getParameter("dossier_depenses") . '/' . $justificatif;
            // Si le chemin du justificatif existe, supprimez également le fichier
            if ($justificatif && $filesystem->exists($pdfPath)) {
                $filesystem->remove($pdfPath);
            }

            $delete_dec = new DeleteDecaissement();
            $delete_dec->setMontant($depense->getMontant())
                    ->setDateSaisie(new \DateTime("now"))
                    ->setClient("depense")
                    ->setTraitePar($depense->getTraitePar()->getPrenom().' '.$depense->getTraitePar()->getNom())
                    ->setDevise($depense->getDevise()->getNomDevise())
                    ->setCaisse($depense->getCaisseRetrait()->getDesignation())
                    ->setDateDecaissement($depense->getDateDepense())
                    ->setCommentaire($depense->getDescription());

            $entityManager->persist($delete_dec);
            $entityManager->remove($depense);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_sorties_depenses_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}
