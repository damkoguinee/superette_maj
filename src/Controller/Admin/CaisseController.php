<?php

namespace App\Controller\Admin;

use App\Entity\Caisse;
use App\Form\CaisseType;
use App\Entity\LieuxVentes;
use App\Repository\CaisseRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/caisse')]
class CaisseController extends AbstractController
{
    #[Route('/accueil', name: 'app_admin_caisse_index', methods: ['GET'])]
    public function index(CaisseRepository $caisseRepository, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/caisse/index.html.twig', [
            'caisses' => $caisseRepository->findAll(),
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/new', name: 'app_admin_caisse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $caisse = new Caisse();
        $form = $this->createForm(CaisseType::class, $caisse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($caisse);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_caisse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/caisse/new.html.twig', [
            'caisse' => $caisse,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/show/{id}', name: 'app_admin_caisse_show', methods: ['GET'])]
    public function show(Caisse $caisse, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/caisse/show.html.twig', [
            'caisse' => $caisse,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_caisse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Caisse $caisse, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(CaisseType::class, $caisse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_caisse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/caisse/edit.html.twig', [
            'caisse' => $caisse,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_caisse_delete', methods: ['POST'])]
    public function delete(Request $request, Caisse $caisse, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$caisse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($caisse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_caisse_index', [], Response::HTTP_SEE_OTHER);
    }
}
