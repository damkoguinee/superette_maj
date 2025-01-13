<?php

namespace App\Controller\Logescom\Entrees;

use App\Entity\Recette;
use App\Form\RecetteType;
use App\Entity\LieuxVentes;
use App\Entity\MouvementCaisse;
use App\Repository\UserRepository;
use App\Repository\RecetteRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/entrees/recette')]
class RecetteController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_entrees_recette_index', methods: ['GET'])]
    public function index(RecetteRepository $recetteRepository, Request $request, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("search")){
            $search = $request->get("search");
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
            $recettes = $recetteRepository->findRecetteByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 15);
        }else{
            $recettes = $recetteRepository->findRecetteByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 15);
        }

        return $this->render('logescom/entrees/recette/index.html.twig', [
            'recettes' => $recettes,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_entrees_recette_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CompteOperationRepository $compteOpRep, CategorieOperationRepository $catetgorieOpRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval($montantString);

            $recette->setLieuVente($lieu_vente)
                        ->setSaisiePar($this->getUser())
                        ->setMontant($montant)
                        ->setDateSaisie(new \DateTime("now"));
            $mouvement_caisse = new MouvementCaisse();
            $categorie_op = $catetgorieOpRep->find(6);
            $compte_op = $compteOpRep->find(5);
            $mouvement_caisse->setCategorieOperation($categorie_op)
                    ->setCompteOperation($compte_op)
                    ->setTypeMouvement("recette")
                    ->setMontant($montant)
                    ->setSaisiePar($this->getUser())
                    ->setDevise($form->getViewData()->getDevise())
                    ->setCaisse($form->getViewData()->getCaisse())
                    ->setModePaie($form->getViewData()->getModePaie())
                    ->setLieuVente($lieu_vente)
                    ->setDateOperation($form->getViewData()->getDateOperation())
                    ->setDateSaisie(new \DateTime("now"));
            $recette->addMouvementCaiss($mouvement_caisse);
            $entityManager->persist($recette);
            $entityManager->flush();

            $this->addFlash("success", "recette enregistrée avec succès :)");
            return $this->redirectToRoute('app_logescom_entrees_recette_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/entrees/recette/new.html.twig', [
            'recette' => $recette,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_entrees_recette_show', methods: ['GET'])]
    public function show(Recette $recette, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/entrees/recette/show.html.twig', [
            'recette' => $recette,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_entrees_recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recette $recette, MouvementCaisseRepository $mouvementCaisseRep, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(RecetteType::class, $recette, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval($montantString);
            $recette->setMontant($montant)
                        ->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

            $mouvement_caisse = $mouvementCaisseRep->findOneBy(['recette' => $recette]); 
            $mouvement_caisse->setMontant($montant)
                ->setDevise($form->getViewData()->getDevise())
                ->setCaisse($form->getViewData()->getCaisse())
                ->setLieuVente($lieu_vente)
                ->setSaisiePar($this->getUser())
                ->setDateOperation($form->getViewData()->getDateOperation())
                ->setDateSaisie(new \DateTime("now"));

            $entityManager->persist($recette);
            $entityManager->flush();

            $this->addFlash("success", "recette modifiée avec succès :)");
            return $this->redirectToRoute('app_logescom_entrees_recette_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/entrees/recette/edit.html.twig', [
            'recette' => $recette,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_entrees_recette_delete', methods: ['POST'])]
    public function delete(Request $request, Recette $recette, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recette->getId(), $request->request->get('_token'))) {
            $entityManager->remove($recette);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_entrees_recette_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}
