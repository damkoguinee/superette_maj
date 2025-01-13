<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use App\Entity\Products;
use App\Form\ProductsType;
use App\Entity\LiaisonProduit;
use App\Form\LiaisonProduitType;
use App\Repository\StockRepository;
use App\Repository\ProductsRepository;
use App\Repository\CategorieRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LiaisonProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MouvementProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/products')]
class ProductsController extends AbstractController
{
    /**
     * Méthode permettant de renvoyer à la vue la liste des produits rangé par catégorie
     */
    #[Route('/', name: 'app_admin_products_index', methods: ['GET'])]
    public function index(ProductsRepository $productsRepository, EntrepriseRepository $entrepriseRep, Request $request): Response
    {
        if ($request->get("search")){
            $search = $request->get("search");

        }else{
            $search = "";
        }
        $pageEncours = $request->get('pageEncours', 1);
        $products = $productsRepository->findProductsAdminPaginated($search, $pageEncours, 100); 
        return $this->render('admin/products/index.html.twig', [
            'products' => $products,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    /**
     * Méthode permettant de créer un nouveau produit
     */

    #[Route('/new', name: 'app_admin_products_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ListeStockRepository $listeStockRep, CategorieRepository $categorieRep, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep, ProductsRepository $productsRep): Response
    {
        $categorie = $categorieRep->find($request->get('categorie', 1));
        $productDetail = new Products();
        $productPaquet = new Products();
        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product, ['categorieId' => $categorie]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $tva = ($form->getData()->getTva())/100;
            $product->setTva($tva);
            $liste_stocks = $listeStockRep->findAll();
            if (!empty($liste_stocks)) {
                foreach ($liste_stocks as $liste_stock) {
                    $stock = new Stock();
                    $stock->setStockProduit($liste_stock)
                        ->setPrixVente($form->getData()->getPrixVente());
                    $product->addStock($stock);
                    $entityManager->persist($stock);
                }
            }
            if ($form->getData()->getNbrePiece()) {
                $productDetail->setCodeBarre(null)
                    ->setReference(($form->getData()->getReference())."det")
                    ->setDesignation(($form->getData()->getDesignation())." détail")
                    ->setCategorie($form->getData()->getCategorie())
                    ->setEpaisseur($form->getData()->getEpaisseur())
                    ->setDimension($form->getData()->getDimension())
                    ->setOrigine($form->getData()->getOrigine())
                    ->setType($form->getData()->getType())
                    ->setcodeBarre($form->getData()->getcodeBarre())
                    ->setTypeProduit("detail")
                    ->setStatut($form->getData()->getStatut())
                    ->setStatutComptable($form->getData()->getStatutComptable())
                    ->setTva($form->getData()->getTva());

                $liaisonProduit1 = new LiaisonProduit();
                $liaisonProduit1->setType("detail");
                $product->addLiaisonProduit1($liaisonProduit1);
                $productDetail->addLiaisonProduit2($liaisonProduit1);

                if (!empty($liste_stocks)) {
                    foreach ($liste_stocks as $liste_stock) {
                        $stock_detail = new Stock();
                        $stock_detail->setStockProduit($liste_stock)
                            ->setPrixVente(null);
                        $productDetail->addStock($stock_detail);
                        $entityManager->persist($stock_detail);
                    }
                }
            }

            if ($form->getData()->getNbrePaquet()) {
                $productPaquet->setCodeBarre(null)
                    ->setReference(($form->getData()->getReference())."paq")
                    ->setDesignation(($form->getData()->getDesignation())." paquet")
                    ->setCategorie($form->getData()->getCategorie())
                    ->setEpaisseur($form->getData()->getEpaisseur())
                    ->setDimension($form->getData()->getDimension())
                    ->setOrigine($form->getData()->getOrigine())
                    ->setType($form->getData()->getType())
                    ->setcodeBarre($form->getData()->getcodeBarre())
                    ->setTypeProduit("paquet")
                    ->setStatut($form->getData()->getStatut())
                    ->setStatutComptable($form->getData()->getStatutComptable())
                    ->setTva($form->getData()->getTva());

                $liaisonProduit2 = new LiaisonProduit();
                $liaisonProduit2->setType("paquet");
                $product->addLiaisonProduit1($liaisonProduit2);
                $productPaquet->addLiaisonProduit2($liaisonProduit2);

                if (!empty($liste_stocks)) {
                    foreach ($liste_stocks as $liste_stock) {
                        $stock_paquet = new Stock();
                        $stock_paquet->setStockProduit($liste_stock)
                            ->setPrixVente(null);
                        $productPaquet->addStock($stock_paquet);
                        $entityManager->persist($stock_paquet);
                    }
                }
            }
            $entityManager->persist($product);
            if ($form->getData()->getNbrePaquet()) {
                $entityManager->persist($productPaquet);
            }
            if ($form->getData()->getNbrePiece()) {
                $entityManager->persist($productDetail);
            }

            $entityManager->flush();
            $this->addFlash("success", "le produit a été crée avec succès :) ");
            return new RedirectResponse($this->generateUrl('app_admin_products_new', ['categorie' => $request->get("categorie")]));
        }

        return $this->render('admin/products/new.html.twig', [
            'product' => $product,
            'form' => $form,
            'productsForm'  => $form->createView(),
            'nameEntite'    =>'products',
            'entite'        =>$product,
            'products' => $productsRep->findBy([], ['id' => "DESC"], 10),
            'categorieId' => $categorie,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_products_show', methods: ['GET', 'POST'])]
    public function show(Products $product, Request $request, EntrepriseRepository $entrepriseRep, ProductsRepository $productsRep, MouvementProductRepository $mouvementProdRep, LiaisonProduitRepository $liaisonProdRep, EntityManagerInterface $entityManager): Response
    {
        if ($request->isXmlHttpRequest()) {            
            $search_product = $request->query->get('search_product');
            if ($search_product) {
                $products = $productsRep->findProductsSearch($search_product);    
                $response = [];
                foreach ($products as $product) {
                    $response[] = [
                        'nom' => $product->getDesignation(),
                        'id' => $product->getId()
                    ]; // Mettez à jour avec le nom réel de votre propriété
                }
                return new JsonResponse($response);
            }
        }
        $liaisonProduit = new LiaisonProduit();
        
        if ($request->get('id_product')) {
            $product2 = $productsRep->find($request->get('id_product'));
            $liaisonProduit->setProduit1($product)
                        ->setProduit2($product2)
                        ->setType($product2->getTypeProduit());
            $entityManager->persist($liaisonProduit);
            $entityManager->flush();
            $liaisonProduit = new LiaisonProduit();
        }
        if ($request->get('id_product_search')) {
            $product2 = $productsRep->find($request->get('id_product_search'));
        }else{
            $product2 = [];
        }
        $liasion_prod = $liaisonProdRep->findLiaisonByProduct($product);
        return $this->render('admin/products/show.html.twig', [
            'product' => $product,
            'nameEntite'    =>'products',
            'entite'        =>$product,
            'entreprise' => $entrepriseRep->find(1),
            'liaisons' => $liasion_prod,
            'liaison_produit' => $liaisonProduit,
            'mouvement_prod' => $mouvementProdRep->findOneBy(['product' => $product]),
            'product2' => $product2
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_products_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Products $product, CategorieRepository $categorieRep, EntityManagerInterface $entityManager, LiaisonProduitRepository $liaisonProdRep, EntrepriseRepository $entrepriseRep): Response
    {

        $categorie = $categorieRep->find($product->getCategorie());
        $form = $this->createForm(ProductsType::class, $product, ['categorieId' => $categorie]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $verif_piece = $liaisonProdRep->findOneBy(['produit1' => $product]);
            // if ($form->getData()->getNbrePiece()) {
            //     $productDetail = new Products();
            //     $productPaquet = new Products();
            //     $productDetail->setCodeBarre(null)
            //         ->setReference(($form->getData()->getReference())."det")
            //         ->setDesignation(($form->getData()->getDesignation())." détail")
            //         ->setCategorie($form->getData()->getCategorie())
            //         ->setEpaisseur($form->getData()->getEpaisseur())
            //         ->setDimension($form->getData()->getDimension())
            //         ->setOrigine($form->getData()->getOrigine())
            //         ->setType($form->getData()->getType())
            //         ->setcodeBarre($form->getData()->getcodeBarre())
            //         ->setTypeProduit("detail")
            //         ->setStatut($form->getData()->getStatut())
            //         ->setStatutComptable($form->getData()->getStatutComptable())
            //         ->setTva($form->getData()->getTva());

            //     $liaisonProduit1 = new LiaisonProduit();
            //     $liaisonProduit1->setType("detail")
            //         ->setProduit1($product);
            //     $productDetail->addLiaisonProduit2($liaisonProduit1);
            //     $entityManager->persist($liaisonProduit1);


            //     if (!empty($liste_stocks)) {
            //         foreach ($liste_stocks as $liste_stock) {
            //             $stock_detail = new Stock();
            //             $stock_detail->setStockProduit($liste_stock)
            //                 ->setPrixVente(null);
            //             $productDetail->addStock($stock_detail);
            //             $entityManager->persist($stock_detail);
            //         }
            //     }
            // }

            // if ($form->getData()->getNbrePaquet()) {
            //     $productPaquet->setCodeBarre(null)
            //         ->setReference(($form->getData()->getReference())."paq")
            //         ->setDesignation(($form->getData()->getDesignation())." paquet")
            //         ->setCategorie($form->getData()->getCategorie())
            //         ->setEpaisseur($form->getData()->getEpaisseur())
            //         ->setDimension($form->getData()->getDimension())
            //         ->setOrigine($form->getData()->getOrigine())
            //         ->setType($form->getData()->getType())
            //         ->setcodeBarre($form->getData()->getcodeBarre())
            //         ->setTypeProduit("paquet")
            //         ->setStatut($form->getData()->getStatut())
            //         ->setStatutComptable($form->getData()->getStatutComptable())
            //         ->setTva($form->getData()->getTva());

            //     $liaisonProduit2 = new LiaisonProduit();
            //     $liaisonProduit2->setType("paquet")
            //         ->setProduit1($product);
            //     $productPaquet->addLiaisonProduit2($liaisonProduit2);
            //     $entityManager->persist($liaisonProduit2);

            //     if (!empty($liste_stocks)) {
            //         foreach ($liste_stocks as $liste_stock) {
            //             $stock_paquet = new Stock();
            //             $stock_paquet->setStockProduit($liste_stock)
            //                 ->setPrixVente(null);
            //             $productPaquet->addStock($stock_paquet);
            //             $entityManager->persist($stock_paquet);
            //         }
            //     }
            // }
            $entityManager->flush();
            $this->addFlash("success", "le produit a bien été modifié :) ");
            return $this->redirectToRoute('app_admin_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/products/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'nameEntite'    =>'products',
            'entite'        =>$product,
            'productsForm'  => $form->createView(),
            'categorieId' => $categorie,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_products_delete', methods: ['POST'])]
    public function delete(Request $request, Products $product, StockRepository $stockRep, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $stocks = $stockRep->findBy(['product' => $product]);
            $entityManager->remove($product);
            foreach ($stocks as $stock) {
                $entityManager->remove($stock);
            }
            $entityManager->flush();
        }
        $this->addFlash("success", "le produit a été supprimé avec succès :) ");
        return $this->redirectToRoute('app_admin_products_index', [], Response::HTTP_SEE_OTHER);
    }
}
