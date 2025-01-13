<?php

namespace App\Controller\Admin;

use App\Entity\Products;
use App\Entity\Categorie;
use App\Entity\Dimensions;
use App\Entity\Epaisseurs;
use App\Form\CategorieType;
use App\Repository\ProductsRepository;
use App\Repository\CategorieRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/categorie')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'app_admin_categorie_index')]
    public function index(CategorieRepository $categorieRepository, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/categorie/index.html.twig', [
            'categories' => $categorieRepository->findBy([], ['nameCategorie' => "ASC"]),
            'entreprise' => $entrepriseRep->find(1)
        ]);
    }

    #[Route('/new', name: 'app_admin_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get("imgProduit")->getData();        
            // Gérez ici le téléchargement des images
            $uploadedImages = [];

            if ($images) {
                foreach ($images as $image) {
                    $nomFichier= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomFichier = $slugger->slug($nomFichier);
                    $nouveauNomFichier .="_".uniqid();
                    $nouveauNomFichier .= "." .$image->guessExtension();
                    $image->move($this->getParameter("dossier_img_products"),$nouveauNomFichier);
                    $uploadedImages[] = $nouveauNomFichier;
                }
                $categorie->setImgProduit($uploadedImages);
            }

            $fichier = $form->get("couverture")->getData();
            if ($fichier) {
                $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$fichier->guessExtension();
                $fichier->move($this->getParameter("dossier_categories"),$nouveauNomFichier);
                $categorie->setCouverture($nouveauNomFichier);
            }

            // dd($request->get('dimension'));
            $dimensions_form = $request->get('dimension');
            $epaisseurs_form = $request->get('epaisseur');
            if ($dimensions_form) {
                foreach ($dimensions_form as  $dimension) {
                    $dimensions = new Dimensions();

                    $dimensions->setValeurDimension($dimension);
                    $categorie->addDimension($dimensions);

                    $entityManager->persist($dimensions);

                }
            }
            if ($epaisseurs_form) {
                foreach ($epaisseurs_form as  $epaisseur) {
                    $epaisseurs = new Epaisseurs();
                    $epaisseurs->setValeurEpaisseur($epaisseur);
                    $categorie->addEpaisseur($epaisseurs);

                    $entityManager->persist($epaisseurs);

                }
            }
            $entityManager->persist($categorie);
            
            $entityManager->flush();
            $this->addFlash("success", "la catégorie a bien été ajoutée");
            return $this->redirectToRoute('app_admin_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
            'nameEntite'=>"categorie",
            'entreprise' => $entrepriseRep->find(1)
        ]);
    }

    #[Route('/{id}', name: 'app_admin_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie, ProductsRepository $productsRepository, EntrepriseRepository $entrepriseRep): Response
    {

        return $this->render('admin/categorie/show.html.twig', [
            'categorie' => $categorie,
            'entite'    => $categorie,
            'nameEntite'=>"categorie",
            'products' => $productsRepository->findBy(['categorie' => $categorie], ['categorie' => "ASC"]),
            'entreprise' => $entrepriseRep->find(1)
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        // dd($request);
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichier =$form->get("couverture")->getData();
            if ($fichier) {
                if ($categorie->getCouverture()) {
                    $ancienFichier=$this->getParameter("dossier_img_products")."/".$categorie->getCouverture();
                    if (file_exists($ancienFichier)) {
                        /**
                          si vous essayer de supprimer un fichier qui n'existe pas, la fonction unlink renvoie une erreur
                         */
                        unlink($ancienFichier);
                    }
                }
                $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$fichier->guessExtension();
                $fichier->move($this->getParameter("dossier_categories"),$nouveauNomFichier);
                $categorie->setCouverture($nouveauNomFichier);

            }

            $images = $form->get("imgProduit")->getData();        
            // Gérez ici le téléchargement des images
            $uploadedImages = [];

            if ($images) {
                $anciennesImages = $categorie->getImgProduit();
                if ($anciennesImages) {
                    foreach ($anciennesImages as $ancienImage) {
                        $ancienFichier = $this->getParameter("dossier_img_products") . "/" . $ancienImage;
                        if (file_exists($ancienFichier)) {
                            unlink($ancienFichier);
                        }
                    }
                }
                foreach ($images as $image) {
                    $nomFichier= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomFichier = $slugger->slug($nomFichier);
                    $nouveauNomFichier .="_".uniqid();
                    $nouveauNomFichier .= "." .$image->guessExtension();
                    $image->move($this->getParameter("dossier_img_products"), $nouveauNomFichier);
                    $uploadedImages[] = $nouveauNomFichier;
                }
                $categorie->setImgProduit($uploadedImages);
            }
            $description_defaut = $request->get("description_defaut");
            if (!$form->getviewData()->getDescription()) {
                $categorie->setDescription($description_defaut);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
            'entite'    => $categorie,
            'nameEntite'=>"categorie",
            'entreprise' => $entrepriseRep->find(1)
        ]);
    }

    #[Route('/{id}', name: 'app_admin_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
