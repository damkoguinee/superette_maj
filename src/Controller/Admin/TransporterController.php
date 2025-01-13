<?php

namespace App\Controller\Admin;

use App\Entity\Transporter;
use App\Form\TransporterType;
use App\Repository\TransporterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/transporter')]
class TransporterController extends AbstractController
{
    #[Route('/', name: 'app_admin_transporter_index', methods: ['GET'])]
    public function index(TransporterRepository $transporterRepository): Response
    {
        return $this->render('admin/transporter/index.html.twig', [
            'transporters' => $transporterRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_transporter_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $transporter = new Transporter();
        $form = $this->createForm(TransporterType::class, $transporter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($transporter);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_transporter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/transporter/new.html.twig', [
            'transporter' => $transporter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_transporter_show', methods: ['GET'])]
    public function show(Transporter $transporter): Response
    {
        return $this->render('admin/transporter/show.html.twig', [
            'transporter' => $transporter,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_transporter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transporter $transporter, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TransporterType::class, $transporter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_transporter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/transporter/edit.html.twig', [
            'transporter' => $transporter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_transporter_delete', methods: ['POST'])]
    public function delete(Request $request, Transporter $transporter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transporter->getId(), $request->request->get('_token'))) {
            $entityManager->remove($transporter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_transporter_index', [], Response::HTTP_SEE_OTHER);
    }
}
