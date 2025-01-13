<?php

namespace App\Controller\Admin;

use App\Entity\BannieresEntreprise;
use App\Form\BannieresEntrepriseType;
use App\Repository\BannieresEntrepriseRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/bannieres/entreprise')]
class BannieresEntrepriseController extends AbstractController
{
    #[Route('/', name: 'app_admin_bannieres_entreprise_index', methods: ['GET'])]
    public function index(BannieresEntrepriseRepository $bannieresEntrepriseRepository, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/bannieres_entreprise/index.html.twig', [
            'bannieres_entreprises' => $bannieresEntrepriseRepository->findAll(),
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/new', name: 'app_admin_bannieres_entreprise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $bannieresEntreprise = new BannieresEntreprise();
        $form = $this->createForm(BannieresEntrepriseType::class, $bannieresEntreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $banniere = $form->get("image")->getData();
            if ($banniere) {
                $nomBanniere= pathinfo($banniere->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNom = $slugger->slug($nomBanniere);
                $nouveauNom .="_".uniqid();
                $nouveauNom .= "." .$banniere->guessExtension();
                $banniere->move($this->getParameter("dossier_banieres"),$nouveauNom);
                $bannieresEntreprise->setImage($nouveauNom);
            }
            $entityManager->persist($bannieresEntreprise);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_bannieres_entreprise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/bannieres_entreprise/new.html.twig', [
            'bannieres_entreprise' => $bannieresEntreprise,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),

        ]);
    }

    #[Route('/{id}', name: 'app_admin_bannieres_entreprise_show', methods: ['GET'])]
    public function show(BannieresEntreprise $bannieresEntreprise): Response
    {
        return $this->render('admin/bannieres_entreprise/show.html.twig', [
            'bannieres_entreprise' => $bannieresEntreprise,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_bannieres_entreprise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BannieresEntreprise $bannieresEntreprise, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(BannieresEntrepriseType::class, $bannieresEntreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_bannieres_entreprise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/bannieres_entreprise/edit.html.twig', [
            'bannieres_entreprise' => $bannieresEntreprise,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),

        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_bannieres_entreprise_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, BannieresEntreprise $bannieresEntreprise, EntityManagerInterface $entityManager): Response
    {
        // if ($this->isCsrfTokenValid('delete'.$bannieresEntreprise->getId(), $request->request->get('_token'))) {
        //     $entityManager->remove($bannieresEntreprise);
        //     $entityManager->flush();
        // }
        $entityManager->remove($bannieresEntreprise);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_bannieres_entreprise_index', [], Response::HTTP_SEE_OTHER);
    }
}
