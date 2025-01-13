<?php

namespace App\Controller\Admin;

use App\Entity\Orders;
use App\Repository\OrdersRepository;
use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/vendeur', name: 'app_admin_')]
class VendeursController extends AbstractController
{
    #[Route('/', name: 'vendeurs')]
    public function index(ShopRepository $shopRepository): Response
    {
        $listeVendeurs = $shopRepository->findBy([], ['id' => "DESC"]);
        $listeVendeursActif = $shopRepository->findBy(['statut' => "actif"]);
        $listeVendeursInActif = $shopRepository->findBy(['statut' => "inactif"]);

        return $this->render('admin/vendeurs/vendeur.html.twig', [
            'listeVendeurs' => $listeVendeurs,
            'listeVendeursActif' => $listeVendeursActif,
            'listeVendeursinActif' => $listeVendeursInActif,
        ]);
    }

    #[Route('/ventes', name: 'ventes')]
    public function ventes(OrdersRepository $ordersRepository): Response
    {
        return $this->render('admin/vendeurs/index.html.twig', [
            'orders' => $ordersRepository->findBy([], ['id' => "DESC"]),
        ]);
    }

    #[Route('/{id}', name: 'ventes_show', methods: ['GET'])]
    public function show(Orders $orders, OrdersRepository $ordersRepository): Response
    {

        return $this->render('admin/vendeurs/show.html.twig', [
            'entite'    => $orders,
            'nameEntite'=>"orders",
            'orders' => $ordersRepository->find($orders),
        ]);
    }

    #[Route('/{id}', name: 'ventes_edit', methods: ['GET'])]
    public function edit(Orders $orders, OrdersRepository $ordersRepository): Response
    {
        return $this->render('admin/vendeurs/index.html.twig', [
            'orders' => $ordersRepository->find($orders),
        ]);
    }

}
