<?php

namespace App\Controller\Admin;

use App\Entity\LieuxVentes;
use App\Form\LieuxVentesType;
use App\Repository\EntrepriseRepository;
use App\Repository\LieuxVentesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/lieux/ventes')]
class LieuxVentesController extends AbstractController
{
    #[Route('/', name: 'app_admin_lieux_ventes_index', methods: ['GET'])]
    public function index(LieuxVentesRepository $lieuxVentesRepository, EntrepriseRepository $entrepriseRepository): Response
    {
        return $this->render('admin/lieux_ventes/index.html.twig', [
            'lieu_ventes' => $lieuxVentesRepository->findAll(),
            'entreprise' => $entrepriseRepository->find(1),

        ]);
    }

    #[Route('/new', name: 'app_admin_lieux_ventes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRepository): Response
    {
        $lieuVente = new LieuxVentes();
        $form = $this->createForm(LieuxVentesType::class, $lieuVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lieuVente);
            $entityManager->flush();
            $this->addFlash("success", "le lieu de vente a été ajouté avec succès");
            return $this->redirectToRoute('app_admin_entreprise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/lieux_ventes/new.html.twig', [
            'lieu_vente' => $lieuVente,
            'entreprise' => $entrepriseRepository->find(1),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_lieux_ventes_show', methods: ['GET'])]
    public function show(LieuxVentes $lieuVente): Response
    {
        return $this->render('admin/lieux_ventes/show.html.twig', [
            'lieu_vente' => $lieuVente,
            'entreprise' => $lieuVente->getEntreprise(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_lieux_ventes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LieuxVentes $lieuVente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LieuxVentesType::class, $lieuVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_entreprise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/lieux_ventes/edit.html.twig', [
            'lieu_vente' => $lieuVente,
            'entreprise' => $lieuVente->getEntreprise(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_lieux_ventes_delete', methods: ['POST'])]
    public function delete(Request $request, LieuxVentes $lieuVente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lieuVente->getId(), $request->request->get('_token'))) {
            $entityManager->remove($lieuVente);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_entreprise_index', [], Response::HTTP_SEE_OTHER);
    }
}
