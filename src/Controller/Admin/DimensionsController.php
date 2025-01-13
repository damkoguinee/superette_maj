<?php

namespace App\Controller\Admin;

use App\Entity\Dimensions;
use App\Form\DimensionsType;
use App\Repository\ProductsRepository;
use App\Repository\CategorieRepository;
use App\Repository\DimensionsRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/dimensions')]
class DimensionsController extends AbstractController
{
    #[Route('/', name: 'app_admin_dimensions_index', methods: ['GET'])]
    public function index(DimensionsRepository $dimensionsRepository, CategorieRepository $categorieRepository, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/dimensions/index.html.twig', [
            'dimensions' => $dimensionsRepository->findAll(),
            'categories' => $categorieRepository->findAll(),
            'nameEntite' => 'dimensions',
            'entreprise' => $entrepriseRep->find(1)
        ]);
    }

    #[Route('/new', name: 'app_admin_dimensions_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $dimension = new Dimensions();
        $form = $this->createForm(DimensionsType::class, $dimension);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dimension);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_dimensions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/dimensions/new.html.twig', [
            'dimension' => $dimension,
            'form' => $form,
            'entite'    => $dimension,
            'nameEntite' => 'dimensions',
            'entreprise' => $entrepriseRep->find(1),

        ]);
    }

    #[Route('/{id}', name: 'app_admin_dimensions_show', methods: ['GET'])]
    public function show(Dimensions $dimension, ProductsRepository $productsRepository, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/dimensions/show.html.twig', [
            'dimension' => $dimension,
            'entite'    => $dimension,
            'nameEntite' => 'dimensions',
            'entreprise' => $entrepriseRep->find(1),
            'products' => $productsRepository->findBy(['dimension' =>$dimension], ['categorie' => "ASC"]),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_dimensions_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Dimensions $dimension, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(DimensionsType::class, $dimension);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_dimensions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/dimensions/edit.html.twig', [
            'dimension' => $dimension,
            'form' => $form,
            'entite'    => $dimension,
            'nameEntite' => 'dimensions',
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_dimensions_delete', methods: ['POST'])]
    public function delete(Request $request, Dimensions $dimension, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dimension->getId(), $request->request->get('_token'))) {
            $entityManager->remove($dimension);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_dimensions_index', [], Response::HTTP_SEE_OTHER);
    }
}
