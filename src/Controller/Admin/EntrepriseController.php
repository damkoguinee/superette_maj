<?php

namespace App\Controller\Admin;

use App\Entity\Entreprise;
use App\Form\EntrepriseType;
use App\Repository\EntrepriseRepository;
use App\Repository\LieuxVentesRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/entreprise')]
class EntrepriseController extends AbstractController
{
    #[Route('/', name: 'app_admin_entreprise_index', methods: ['GET'])]
    public function index(EntrepriseRepository $entrepriseRepository): Response
    {
        return $this->render('admin/entreprise/index.html.twig', [
            'entreprise' => $entrepriseRepository->find(1),
            'entreprises' => $entrepriseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_entreprise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entreprise->setIdentifiant("cl00001");
            $logo = $form->get("logo")->getData();
            if ($logo) {
                $nomFichier= pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$logo->guessExtension();
                $logo->move($this->getParameter("dossier_img_logos"),$nouveauNomFichier);
                $entreprise->setLogo($nouveauNomFichier);
            }
            $entityManager->persist($entreprise);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_entreprise_show', ['id' =>$entreprise->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/entreprise/new.html.twig', [
            'entreprise' => $entreprise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_entreprise_show', methods: ['GET'])]
    public function show(Entreprise $entreprise, LieuxVentesRepository $lieuxVentesRep, ListeStockRepository $listeStockRep): Response
    {
        return $this->render('admin/entreprise/show.html.twig', [
            'entreprise' => $entreprise,
            'lieux_ventes' => $lieuxVentesRep->findAll(),
            'listes_stocks' => $listeStockRep->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_entreprise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entreprise $entreprise, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logo =$form->get("logo")->getData();
            if ($logo) {
                if ($entreprise->getLogo()) {
                    $ancienLogo=$this->getParameter("dossier_img_logos")."/".$entreprise->getLogo();
                    if (file_exists($ancienLogo)) {
                        unlink($ancienLogo);
                    }
                }
                $nomLogo= pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomLogo = $slugger->slug($nomLogo);
                $nouveauNomLogo .="_".uniqid();
                $nouveauNomLogo .= "." .$logo->guessExtension();
                $logo->move($this->getParameter("dossier_img_logos"),$nouveauNomLogo);
                $entreprise->setLogo($nouveauNomLogo);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_admin_entreprise_show', ['id' =>$entreprise->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->render('admin/entreprise/edit.html.twig', [
            'entreprise' => $entreprise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_entreprise_delete', methods: ['POST'])]
    public function delete(Request $request, Entreprise $entreprise, Filesystem $filesystem, EntityManagerInterface $entityManager): Response
    {
        $logoFileName = $entreprise->getLogo();
        $logoPath = $this->getParameter("dossier_img_logos") . '/' . $logoFileName;
        // Si le chemin du fichier PDF existe, supprimez également le fichier
        if ($logoFileName && $filesystem->exists($logoPath)) {
            $filesystem->remove($logoPath);
        }

        if ($this->isCsrfTokenValid('delete'.$entreprise->getId(), $request->request->get('_token'))) {
            $entityManager->remove($entreprise);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_entreprise_show', ['id' =>$entreprise->getId()], Response::HTTP_SEE_OTHER);

    }
}
