<?php

namespace App\Controller\Logescom\Stock;

use App\Entity\AnomalieProduit;
use App\Entity\LieuxVentes;
use App\Form\AnomalieProduitType;
use App\Repository\AnomalieProduitRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use App\Repository\MouvementProductRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/logescom/stock/anomalie/produit')]
class AnomalieProduitController extends AbstractController
{
    #[Route('/', name: 'app_logescom_stock_anomalie_produit_index', methods: ['GET'])]
    public function index(AnomalieProduitRepository $anomalieProduitRepository): Response
    {
        return $this->render('logescom/stock/anomalie_produit/index.html.twig', [
            'anomalie_produits' => $anomalieProduitRepository->findAll(),
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_stock_anomalie_produit_new', methods: ['GET', 'POST'])]
    public function new(LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep, AnomalieProduitRepository $anomalieRep, ListeStockRepository $listeStockRep, Request $request, EntityManagerInterface $entityManager): Response
    {
        $anomalieProduit = new AnomalieProduit();
        $stocks = $listeStockRep->findBy(['lieuVente' => $lieu_vente]);
        $form = $this->createForm(AnomalieProduitType::class, $anomalieProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($anomalieProduit);
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_stock_anomalie_produit_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('logescom/stock/anomalie_produit/new.html.twig', [
            'anomalie_produit' => $anomalieProduit,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'anomalie_produits' => $anomalieRep->findBy(['stock' => $stocks], ['dateAnomalie' => 'DESC', 'stock' => 'ASC']),
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_stock_anomalie_produit_show', methods: ['GET'])]
    public function show(AnomalieProduit $anomalieProduit, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/stock/anomalie_produit/show.html.twig', [
            'anomalie_produit' => $anomalieProduit,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $anomalieProduit->getStock()->getLieuVente(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_stock_anomalie_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AnomalieProduit $anomalieProduit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnomalieProduitType::class, $anomalieProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_stock_anomalie_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/stock/anomalie_produit/edit.html.twig', [
            'anomalie_produit' => $anomalieProduit,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_logescom_stock_anomalie_produit_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, AnomalieProduit $anomalieProduit, StockRepository $stockRep, EntityManagerInterface $entityManager): Response
    {
        // if ($this->isCsrfTokenValid('delete'.$anomalieProduit->getId(), $request->request->get('_token'))) {
            // la suppression est gérée sur l'entité AnomalieProduit.php avec cascade remove et orphan

        $stock = $stockRep->findOneBy(['product' => $anomalieProduit->getProduct(), 'stockProduit' => $anomalieProduit->getStock() ]);

        $stock->setQuantite($stock->getQuantite() - $anomalieProduit->getQuantite());
        $entityManager->persist($stock);
        $entityManager->remove($anomalieProduit);
        $entityManager->flush();

        if ($request->get('origine') == 'stock') {
            return $this->redirectToRoute('app_logescom_stock_gestion_stock_show', ['stock' => $stock->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_logescom_stock_anomalie_produit_new', ['lieu_vente' => $anomalieProduit->getStock()->getLieuVente()->getId()], Response::HTTP_SEE_OTHER);
    }
}
