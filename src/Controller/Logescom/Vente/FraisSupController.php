<?php

namespace App\Controller\Logescom\Vente;

use App\Entity\FraisSup;
use App\Form\FraisSupType;
use App\Repository\FraisSupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/logescom/vente/frais/sup')]
class FraisSupController extends AbstractController
{
    #[Route('/', name: 'app_logescom_vente_frais_sup_index', methods: ['GET'])]
    public function index(FraisSupRepository $fraisSupRepository): Response
    {
        return $this->render('logescom/vente/frais_sup/index.html.twig', [
            'frais_sups' => $fraisSupRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_logescom_vente_frais_sup_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fraisSup = new FraisSup();
        $form = $this->createForm(FraisSupType::class, $fraisSup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fraisSup);
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_vente_frais_sup_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/vente/frais_sup/new.html.twig', [
            'frais_sup' => $fraisSup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_vente_frais_sup_show', methods: ['GET'])]
    public function show(FraisSup $fraisSup): Response
    {
        return $this->render('logescom/vente/frais_sup/show.html.twig', [
            'frais_sup' => $fraisSup,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_vente_frais_sup_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FraisSup $fraisSup, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FraisSupType::class, $fraisSup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_vente_frais_sup_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/vente/frais_sup/edit.html.twig', [
            'frais_sup' => $fraisSup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_vente_frais_sup_delete', methods: ['POST'])]
    public function delete(Request $request, FraisSup $fraisSup, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fraisSup->getId(), $request->request->get('_token'))) {
            $entityManager->remove($fraisSup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_vente_frais_sup_index', [], Response::HTTP_SEE_OTHER);
    }
}
