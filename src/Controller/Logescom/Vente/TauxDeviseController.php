<?php

namespace App\Controller\Logescom\Vente;

use App\Entity\TauxDevise;
use App\Form\TauxDeviseType;
use App\Repository\TauxDeviseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/logescom/vente/taux/devise')]
class TauxDeviseController extends AbstractController
{
    #[Route('/', name: 'app_logescom_vente_taux_devise_index', methods: ['GET'])]
    public function index(TauxDeviseRepository $tauxDeviseRepository): Response
    {
        return $this->render('logescom/vente/taux_devise/index.html.twig', [
            'taux_devises' => $tauxDeviseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_logescom_vente_taux_devise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tauxDevise = new TauxDevise();
        $form = $this->createForm(TauxDeviseType::class, $tauxDevise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tauxDevise);
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_vente_taux_devise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/vente/taux_devise/new.html.twig', [
            'taux_devise' => $tauxDevise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_vente_taux_devise_show', methods: ['GET'])]
    public function show(TauxDevise $tauxDevise): Response
    {
        return $this->render('logescom/vente/taux_devise/show.html.twig', [
            'taux_devise' => $tauxDevise,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_vente_taux_devise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TauxDevise $tauxDevise, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TauxDeviseType::class, $tauxDevise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_vente_taux_devise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/vente/taux_devise/edit.html.twig', [
            'taux_devise' => $tauxDevise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_vente_taux_devise_delete', methods: ['POST'])]
    public function delete(Request $request, TauxDevise $tauxDevise, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tauxDevise->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tauxDevise);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_vente_taux_devise_index', [], Response::HTTP_SEE_OTHER);
    }
}
