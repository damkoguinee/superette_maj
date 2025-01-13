<?php

namespace App\Controller\Logescom\Caisse;

use App\Entity\ClotureCaisse;
use App\Form\ClotureCaisseType;
use App\Repository\ClotureCaisseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/logescom/caisse/cloture/caisse')]
class ClotureCaisseController extends AbstractController
{
    #[Route('/', name: 'app_logescom_caisse_cloture_caisse_index', methods: ['GET'])]
    public function index(ClotureCaisseRepository $clotureCaisseRepository): Response
    {
        return $this->render('logescom/caisse/cloture_caisse/index.html.twig', [
            'cloture_caisses' => $clotureCaisseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_logescom_caisse_cloture_caisse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clotureCaisse = new ClotureCaisse();
        $form = $this->createForm(ClotureCaisseType::class, $clotureCaisse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($clotureCaisse);
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_caisse_cloture_caisse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/caisse/cloture_caisse/new.html.twig', [
            'cloture_caisse' => $clotureCaisse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_caisse_cloture_caisse_show', methods: ['GET'])]
    public function show(ClotureCaisse $clotureCaisse): Response
    {
        return $this->render('logescom/caisse/cloture_caisse/show.html.twig', [
            'cloture_caisse' => $clotureCaisse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_caisse_cloture_caisse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ClotureCaisse $clotureCaisse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClotureCaisseType::class, $clotureCaisse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_caisse_cloture_caisse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/caisse/cloture_caisse/edit.html.twig', [
            'cloture_caisse' => $clotureCaisse,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_logescom_caisse_cloture_caisse_delete', methods: ['POST'])]
    public function delete(Request $request, ClotureCaisse $clotureCaisse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$clotureCaisse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($clotureCaisse);
            $entityManager->flush();
            $this->addFlash("success", "Clôture supprimée avec succés :)");
        }

        return $this->redirectToRoute('app_logescom_caisse_cloture', ['lieu_vente' => $clotureCaisse->getLieuVente()->getId()], Response::HTTP_SEE_OTHER);
    }
}
