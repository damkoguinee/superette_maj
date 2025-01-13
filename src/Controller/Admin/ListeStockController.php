<?php

namespace App\Controller\Admin;

use App\Entity\ListeStock;
use App\Entity\Stock;
use App\Form\ListeStockType;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/liste/stock')]
class ListeStockController extends AbstractController
{
    #[Route('/', name: 'app_admin_liste_stock_index', methods: ['GET'])]
    public function index(ListeStockRepository $listeStockRepository): Response
    {
        return $this->render('admin/liste_stock/index.html.twig', [
            'liste_stocks' => $listeStockRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_liste_stock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductsRepository $productsRep,  EntrepriseRepository $entrepriseRep, EntityManagerInterface $entityManager): Response
    {
        $listeStock = new ListeStock();
        $form = $this->createForm(ListeStockType::class, $listeStock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $products = $productsRep->findAll();
            // on prepare l'insertion des produits dans le nouveau stock crée
            foreach ($products as $product) {
                $stock = new Stock();                
                $stock->setProducts($product)
                    ->setPrixVente($product->getPrixVente());
                $listeStock->addStockProduit($stock);
                $entityManager->persist($stock);

            }

            $entityManager->persist($listeStock);
            $entityManager->flush();

            $this->addFlash("success","Enregistrement éffectué avec succès !!!");
            return $this->redirectToRoute('app_admin_entreprise_show', ['id' =>$listeStock->getLieuVente()->getEntreprise()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/liste_stock/new.html.twig', [
            'liste_stock' => $listeStock,
            'entreprise' => $entrepriseRep->find(1),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_liste_stock_show', methods: ['GET'])]
    public function show(ListeStock $listeStock): Response
    {
        return $this->render('admin/liste_stock/show.html.twig', [
            'liste_stock' => $listeStock,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_liste_stock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ListeStock $listeStock, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ListeStockType::class, $listeStock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->flush();
            $this->addFlash("success","Modification éffectuée avec succès !!!");
            return $this->redirectToRoute('app_admin_entreprise_show', ['id' =>$listeStock->getLieuVente()->getEntreprise()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/liste_stock/edit.html.twig', [
            'liste_stock' => $listeStock,
            'entreprise' => $listeStock->getLieuVente()->getEntreprise(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_liste_stock_delete', methods: ['POST'])]
    public function delete(Request $request, ListeStock $listeStock, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$listeStock->getId(), $request->request->get('_token'))) {
            $entityManager->remove($listeStock);
            $entityManager->flush();
        }

        $this->addFlash("success","Suppression éffectuée avec succès !!!");
            return $this->redirectToRoute('app_admin_entreprise_show', ['id' =>$listeStock->getLieuVente()->getEntreprise()->getId()], Response::HTTP_SEE_OTHER);
    }
}
