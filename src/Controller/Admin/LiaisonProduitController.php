<?php

namespace App\Controller\Admin;

use App\Entity\LiaisonProduit;
use App\Form\LiaisonProduitType;
use App\Repository\LiaisonProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/liaison/produit')]
class LiaisonProduitController extends AbstractController
{
    #[Route('/', name: 'app_admin_liaison_produit_index', methods: ['GET'])]
    public function index(LiaisonProduitRepository $liaisonProduitRepository): Response
    {
        return $this->render('admin/liaison_produit/index.html.twig', [
            'liaison_produits' => $liaisonProduitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_liaison_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $liaisonProduit = new LiaisonProduit();
        $form = $this->createForm(LiaisonProduitType::class, $liaisonProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($liaisonProduit);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_liaison_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/liaison_produit/new.html.twig', [
            'liaison_produit' => $liaisonProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_liaison_produit_show', methods: ['GET'])]
    public function show(LiaisonProduit $liaisonProduit): Response
    {
        return $this->render('admin/liaison_produit/show.html.twig', [
            'liaison_produit' => $liaisonProduit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_liaison_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LiaisonProduit $liaisonProduit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LiaisonProduitType::class, $liaisonProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_liaison_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/liaison_produit/edit.html.twig', [
            'liaison_produit' => $liaisonProduit,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_liaison_produit_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, LiaisonProduit $liaisonProduit, EntityManagerInterface $entityManager): Response
    {
        // if ($this->isCsrfTokenValid('delete'.$liaisonProduit->getId(),  $request->request->get('_token'))) {
        //     $entityManager->remove($liaisonProduit);
        //     $entityManager->flush();
        // }

        $entityManager->remove($liaisonProduit);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_products_show', ['id' => $liaisonProduit->getProduit1()->getId()], Response::HTTP_SEE_OTHER);
    }
}
