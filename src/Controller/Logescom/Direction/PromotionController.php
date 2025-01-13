<?php

namespace App\Controller\Logescom\Direction;

use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Repository\EntrepriseRepository;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/logescom/direction/promotion')]
class PromotionController extends AbstractController
{
    #[Route('/', name: 'app_logescom_direction_promotion_index', methods: ['GET'])]
    public function index(PromotionRepository $promotionRepository, EntrepriseRepository $entrepriseRep): Response
    {
        $promotions = $promotionRepository->findAll();
        // dd($promotions);
        return $this->render('logescom/direction/promotion/index.html.twig', [
            'promotions' => $promotionRepository->findAll(),
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),
        ]);
    }

    #[Route('/new', name: 'app_logescom_direction_promotion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promotion->setDateSaisie(new \DateTime("now"))
                    ->setSaisiePar($this->getUser());
            $entityManager->persist($promotion);
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_direction_promotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/direction/promotion/new.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_direction_promotion_show', methods: ['GET'])]
    public function show(Promotion $promotion, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/direction/promotion/show.html.twig', [
            'promotion' => $promotion,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_direction_promotion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Promotion $promotion, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promotion->setDateSaisie(new \DateTime("now"))
                    ->setSaisiePar($this->getUser());
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_direction_promotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/direction/promotion/edit.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_direction_promotion_delete', methods: ['POST'])]
    public function delete(Request $request, Promotion $promotion, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promotion->getId(), $request->request->get('_token'))) {
            $entityManager->remove($promotion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_direction_promotion_index', [], Response::HTTP_SEE_OTHER);
    }
}
