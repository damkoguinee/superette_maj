<?php

namespace App\Controller\Logescom\Admin;

use App\Entity\LieuxVentes;
use App\Entity\CategorieRecette;
use App\Form\CategorieRecetteType;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategorieRecetteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/admin/categorie/recette')]
class CategorieRecetteController extends AbstractController
{
    #[Route('/', name: 'app_logescom_admin_categorie_recette_index', methods: ['GET'])]
    public function index(CategorieRecetteRepository $categorieRecetteRepository): Response
    {
        return $this->render('logescom/admin/categorie_recette/index.html.twig', [
            'categorie_recettes' => $categorieRecetteRepository->findAll(),
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_admin_categorie_recette_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntrepriseRepository $entrepriseRep, LieuxVentes $lieu_vente, EntityManagerInterface $entityManager): Response
    {
        $categorieRecette = new CategorieRecette();
        $form = $this->createForm(CategorieRecetteType::class, $categorieRecette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieRecette);
            $entityManager->flush();

            $this->addFlash("success", "Catégorie ajoutée avec succès :)");
            return $this->redirectToRoute('app_logescom_entrees_recette_new', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/admin/categorie_recette/new.html.twig', [
            'categorie_recette' => $categorieRecette,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_admin_categorie_recette_show', methods: ['GET'])]
    public function show(CategorieRecette $categorieRecette): Response
    {
        return $this->render('logescom/admin/categorie_recette/show.html.twig', [
            'categorie_recette' => $categorieRecette,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_admin_categorie_recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieRecette $categorieRecette, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieRecetteType::class, $categorieRecette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_admin_categorie_recette_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/admin/categorie_recette/edit.html.twig', [
            'categorie_recette' => $categorieRecette,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_admin_categorie_recette_delete', methods: ['POST'])]
    public function delete(Request $request, CategorieRecette $categorieRecette, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorieRecette->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorieRecette);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_admin_categorie_recette_index', [], Response::HTTP_SEE_OTHER);
    }
}
