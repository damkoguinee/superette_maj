<?php

namespace App\Controller\Logescom\Admin;

use App\Entity\FraisSup;
use App\Entity\LieuxVentes;
use App\Form\FraisSup1Type;
use App\Repository\FraisSupRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/admin/frais/sup')]
class FraisSupController extends AbstractController
{
    #[Route('/', name: 'app_logescom_admin_frais_sup_index', methods: ['GET'])]
    public function index(FraisSupRepository $fraisSupRepository): Response
    {
        return $this->render('logescom/admin/frais_sup/index.html.twig', [
            'frais_sups' => $fraisSupRepository->findAll(),
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_admin_frais_sup_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntrepriseRepository $entrepriseRep, LieuxVentes $lieu_vente, EntityManagerInterface $entityManager): Response
    {
        $fraisSup = new FraisSup();
        $form = $this->createForm(FraisSup1Type::class, $fraisSup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fraisSup);
            $entityManager->flush();
            $this->addFlash("success", "frais sup ajouté avec succès :)");
            return $this->redirectToRoute('app_logescom_vente_facturation_vente', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/admin/frais_sup/new.html.twig', [
            'frais_sup' => $fraisSup,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_admin_frais_sup_show', methods: ['GET'])]
    public function show(FraisSup $fraisSup): Response
    {
        return $this->render('logescom/admin/frais_sup/show.html.twig', [
            'frais_sup' => $fraisSup,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_admin_frais_sup_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FraisSup $fraisSup, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FraisSup1Type::class, $fraisSup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_admin_frais_sup_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/admin/frais_sup/edit.html.twig', [
            'frais_sup' => $fraisSup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_admin_frais_sup_delete', methods: ['POST'])]
    public function delete(Request $request, FraisSup $fraisSup, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fraisSup->getId(), $request->request->get('_token'))) {
            $entityManager->remove($fraisSup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_admin_frais_sup_index', [], Response::HTTP_SEE_OTHER);
    }
}
