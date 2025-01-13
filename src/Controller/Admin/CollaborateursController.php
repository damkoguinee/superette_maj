<?php

namespace App\Controller\Admin;

use App\Entity\Collaborateurs;
use App\Form\CollaborateursType;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CollaborateursRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/collaborateurs')]
class CollaborateursController extends AbstractController
{
    #[Route('/', name: 'app_admin_collaborateurs_index', methods: ['GET'])]
    public function index(CollaborateursRepository $collaborateursRepository, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/collaborateurs/index.html.twig', [
            'collaborateurs' => $collaborateursRepository->findAll(),
            'entreprise' => $entrepriseRep->find(1),

        ]);
    }

    #[Route('/new', name: 'app_admin_collaborateurs_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $collaborateur = new Collaborateurs();
        $form = $this->createForm(CollaborateursType::class, $collaborateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logo = $form->get("logo")->getData();
            if ($logo) {
                $nomLogo= pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNom = $slugger->slug($nomLogo);
                $nouveauNom .="_".uniqid();
                $nouveauNom .= "." .$logo->guessExtension();
                $logo->move($this->getParameter("dossier_img_logos"),$nouveauNom);
                $collaborateur->setLogo($nouveauNom);
            }
            $entityManager->persist($collaborateur);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_collaborateurs_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/collaborateurs/new.html.twig', [
            'collaborateur' => $collaborateur,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),

        ]);
    }

    #[Route('/{id}', name: 'app_admin_collaborateurs_show', methods: ['GET'])]
    public function show(Collaborateurs $collaborateur): Response
    {
        return $this->render('admin/collaborateurs/show.html.twig', [
            'collaborateur' => $collaborateur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_collaborateurs_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Collaborateurs $collaborateur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CollaborateursType::class, $collaborateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_collaborateurs_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/collaborateurs/edit.html.twig', [
            'collaborateur' => $collaborateur,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_collaborateurs_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, Collaborateurs $collaborateur, EntityManagerInterface $entityManager): Response
    {
        // if ($this->isCsrfTokenValid('delete'.$collaborateur->getId(), $request->request->get('_token'))) {
        //     $entityManager->remove($collaborateur);
        //     $entityManager->flush();
        // }

        $entityManager->remove($collaborateur);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_collaborateurs_index', [], Response::HTTP_SEE_OTHER);
    }
}
