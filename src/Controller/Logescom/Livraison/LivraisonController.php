<?php

namespace App\Controller\Logescom\Livraison;

use App\Entity\CommandeProduct;
use App\Entity\Livraison;
use App\Entity\Facturation;
use App\Entity\LieuxVentes;
use App\Entity\MouvementProduct;
use App\Repository\UserRepository;
use App\Repository\StockRepository;
use App\Repository\ProductsRepository;
use App\Repository\LivraisonRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FacturationRepository;
use App\Repository\CommandeProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/logescom/livraison/livraison')]
class LivraisonController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_livraison_livraison_index')]
    public function index(LieuxVentes $lieu_vente, Request $request, UserRepository $userRep, AuthorizationCheckerInterface $authorizationChecker, LivraisonRepository $livraisonRep, EntrepriseRepository $entrepriseRep): Response
    {
        
        if ($request->get("id_client_search")){
            $search = $request->get("id_client_search");
        }else{
            $search = "";
        }

        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-m-d");
            $date2 = date("Y-m-d");
        }

        if ($request->isXmlHttpRequest()) {
            if ( $request->query->get('search')) {
                $search = $request->query->get('search');
                $clients = $userRep->findClientSearchByLieu($search, $lieu_vente);    
                $response = [];
                foreach ($clients as $client) {
                    $response[] = [
                        'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                        'id' => $client->getId()
                    ]; // Mettez à jour avec le nom réel de votre propriété
                }
                return new JsonResponse($response);
            }

            if ( $request->query->get('search_personnel')) {
                $search = $request->query->get('search_personnel');
                $clients = $userRep->findPersonnelSearchByLieu($search, $lieu_vente);    
                $response = [];
                foreach ($clients as $client) {
                    $response[] = [
                        'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                        'id' => $client->getId()
                    ]; // Mettez à jour avec le nom réel de votre propriété
                }
                return new JsonResponse($response);
            }
        }
        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("id_client_search")){
           $livraisons = $livraisonRep->findLivraisonByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 100);
        }elseif ($request->get("id_personnel")){
           $livraisons = $livraisonRep->findLivraisonByLieuByPersonnelPaginated($lieu_vente, $request->get("id_personnel"), $date1, $date2, $pageEncours, 100);
        }else{
            if (!$authorizationChecker->isGranted('ROLE_GESTIONNAIRE')) {
               $livraisons = $livraisonRep->findLivraisonByLieuByPersonnelPaginated($lieu_vente, $this->getUser(), $date1, $date2, $pageEncours, 100);
            }else{
               $livraisons = $livraisonRep->findLivraisonByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 100);
            }
        }
        

        return $this->render('logescom/livraison/livraison/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'livraisons' =>$livraisons,
            'search' => $search,
            'date1' => $date1,
            'date2' => $date2,
        ]);
    }
    
    #[Route('/facture/non-livre/{lieu_vente}', name: 'app_logescom_livraison_livraison_facture')]
    public function facturationsNonLivres(LieuxVentes $lieu_vente, Request $request, UserRepository $userRep, AuthorizationCheckerInterface $authorizationChecker, FacturationRepository $facturationRep, EntrepriseRepository $entrepriseRep): Response
    {
        
        if ($request->get("id_client_search")){
            $search = $request->get("id_client_search");
        }else{
            $search = "";
        }

        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        if ($request->isXmlHttpRequest()) {
            if ( $request->query->get('search')) {
                $search = $request->query->get('search');
                $clients = $userRep->findClientSearchByLieu($search, $lieu_vente);    
                $response = [];
                foreach ($clients as $client) {
                    $response[] = [
                        'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                        'id' => $client->getId()
                    ]; // Mettez à jour avec le nom réel de votre propriété
                }
                return new JsonResponse($response);
            }

            if ( $request->query->get('search_personnel')) {
                $search = $request->query->get('search_personnel');
                $clients = $userRep->findPersonnelSearchByLieu($search, $lieu_vente);    
                $response = [];
                foreach ($clients as $client) {
                    $response[] = [
                        'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                        'id' => $client->getId()
                    ]; // Mettez à jour avec le nom réel de votre propriété
                }
                return new JsonResponse($response);
            }
        }
        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("id_client_search")){
            $facturations = $facturationRep->findFacturationByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 100);
        }elseif ($request->get("id_personnel")){
            $facturations = $facturationRep->findFacturationByLieuByPersonnelPaginated($lieu_vente, $request->get("id_personnel"), $date1, $date2, $pageEncours, 100);
        }elseif ($request->get("numero_facture")){
            $facturations = $facturationRep->findFacturationByNumeroPaginated($request->get("numero_facture"), $pageEncours, 100);
        }else{
            if (!$authorizationChecker->isGranted('ROLE_GESTIONNAIRE')) {
                $facturations = $facturationRep->findFacturationNotDelivredByLieuByPersonnelPaginated($lieu_vente, $this->getUser(), $date1, $date2, $pageEncours, 100);
            }else{
                $facturations = $facturationRep->findFacturationNotDelivredByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 100);
            }
        }
        
        // dd($facturations);
        return $this->render('logescom/livraison/livraison/facture_non_livre.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'facturations' => $facturations,
            'search' => $search,
            'date1' => $date1,
            'date2' => $date2,
        ]);
    }

    #[Route('/new/{id}/{lieu_vente}', name: 'app_logescom_livraison_livraison_new')]
    public function new(Facturation $facturation, LieuxVentes $lieu_vente, Request $request, EntrepriseRepository $entrepriseRep, StockRepository $stockRep, ProductsRepository $productRep, ListeStockRepository $listeStockRep,  CommandeProductRepository $commandeProdRep, LivraisonRepository $livraisonRep, EntityManagerInterface $em): Response
    {        
        $commandes = [];
        $commandes_prod = $commandeProdRep->findBy(['facturation' => $facturation], ['quantite' => 'ASC']);
        foreach ($commandes_prod as $value) {
            $stocks = $stockRep->sumQuantiteByProductByLieu($value->getProduct() , $lieu_vente);

            $commandes[] = [
                'vente' => $value,
                'stocks' => $stocks
            ];
        }
        $livraisons = $livraisonRep->findBy(['commande' => $commandes_prod], ['commande' => 'ASC']);
        return $this->render('logescom/livraison/livraison/livraison_facture.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'facturation' => $facturation,
            'commandes' => $commandes,
            'livraisons' => $livraisons,
            'listeStocks' => $listeStockRep->findBy(['lieuVente' => $lieu_vente]),
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_livraison_livraison_edit')]
    public function edit(CommandeProduct $commande, LieuxVentes $lieu_vente, Request $request,  StockRepository $stockRep, ProductsRepository $productRep, ListeStockRepository $listeStockRep,  CommandeProductRepository $commandeProdRep, EntityManagerInterface $em): Response
    {
        if ($request->get('stock')) {
            $qtite =  floatval(preg_replace('/[^0-9,.]/', '', $request->get('qtite')));
            $commentaire = $request->get('commentaire');
            $dateLivraison = new \DateTime($request->get('date'));

            $id_product = $request->get('id_product');
            $product = $productRep->find($id_product);

            $id_emplacement = $request->get('stock');
            $emplacement = $listeStockRep->find($id_emplacement);
            $stock = $stockRep->findOneBy(['product' => $product, 'stockProduit' => $emplacement]);
            $qtite_dispo = $stock->getQuantite();

            // $commande = $commandeProdRep->findOneBy(['facturation' => $facturation, 'product' => $product], ['quantite' => 'ASC']);
            $facturation = $commande->getFacturation();

            $qtite_cmd = $commande->getQuantite();

            if ($qtite <= $qtite_cmd) {
                if ($qtite_dispo >= $qtite) {
                    $stock->setQuantite($stock->getQuantite() - $qtite);
                    $em->persist($stock);


                    $commande->setQuantiteLivre($commande->getQuantiteLivre() + $qtite);
                    $em->persist($commande);

                    $verif_livraison = $commandeProdRep->findBy(['facturation' => $facturation]);
                    $etat = 'livré';
                    foreach ($verif_livraison as $verif) {
                        if (($verif->getQuantite() - $verif->getQuantiteLivre()) != 0) {
                            $etat = 'en-cours';
                            break;
                        }
                    }
                    $facturation->setEtatLivraison($etat);
                    $em->persist($facturation);

                    $livraison = new Livraison();
                    $livraison->setQuantiteLiv($qtite)
                            ->setSaisiePar($this->getUser())
                            ->setStock($emplacement)
                            ->setCommentaire($commentaire ? $commentaire : "livraison différée")
                            ->setDateLivraison($dateLivraison)
                            ->setDateSaisie(new \DateTime("now"));
                    $commande->addLivraison($livraison);

                    $mouvementProduct = new MouvementProduct();
                    $mouvementProduct->setStockProduct($emplacement)
                                ->setProduct($product)
                                ->setQuantite(-$qtite)
                                ->setLieuVente($lieu_vente)
                                ->setPersonnel($this->getUser())
                                ->setDateOperation($dateLivraison)
                                ->setOrigine("livraison différée")
                                ->setDescription($commentaire ? $commentaire : "livraison différée")
                                ->setClient($commande->getFacturation()->getClient());
                    $livraison->addMouvementProduct($mouvementProduct);
                    $em->persist($livraison);
                    $em->flush();
                    $this->addFlash("success", 'Livraison éffetuée avec succès :)');
                }else{
                    $this->addFlash("warning", 'vous devez saisir une quantité inférieure ou égale à '.$stock->getQuantite());
                }
            }else{
                $this->addFlash("warning", 'vous devez saisir une quantité inférieure ou égale à '.$commande->getQuantite());
            }
        }
        return $this->redirectToRoute('app_logescom_livraison_livraison_new', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        
    }

    #[Route('/livraison/globale/{id}/{lieu_vente}', name: 'app_logescom_livraison_livraison_livraison_globale')]
    public function livraisonGlobale(Facturation $facturation, LieuxVentes $lieu_vente, Request $request,  StockRepository $stockRep, ProductsRepository $productRep, ListeStockRepository $listeStockRep,  CommandeProductRepository $commandeProdRep, EntityManagerInterface $em): Response
    {
        
        // livraison générale 

        if ($request->get('stock_general')) {
            $commentaire = $request->get('commentaire');
            $dateLivraison = new \DateTime($request->get('date'));

            $id_emplacement = $request->get('stock_general');
            $emplacement = $listeStockRep->find($id_emplacement);

            $commandes = $commandeProdRep->findBy(['facturation' => $facturation]);

            foreach ($commandes as $commande) {
                $qtite = $commande->getQuantite() - $commande->getQuantiteLivre();

                if ($qtite > 0) {

                    $stock = $stockRep->findOneBy(['product' => $commande->getProduct(), 'stockProduit' => $emplacement]);
    
                    $stock->setQuantite($stock->getQuantite() - $qtite);
                    $em->persist($stock);
        
                    $commande->setQuantiteLivre($commande->getQuantiteLivre() + $qtite);
                    $em->persist($commande);
        
                    $verif_livraison = $commandeProdRep->findBy(['facturation' => $facturation]);
                    $etat = 'livré';
                    foreach ($verif_livraison as $verif) {
                        if (($verif->getQuantite() - $verif->getQuantiteLivre()) != 0) {
                            $etat = 'en-cours';
                            break;
                        }
                    }
                    $facturation->setEtatLivraison($etat);
                    $em->persist($facturation);
        
                    $livraison = new Livraison();
                    $livraison->setQuantiteLiv($qtite)
                            ->setSaisiePar($this->getUser())
                            ->setStock($emplacement)
                            ->setCommentaire($commentaire ? $commentaire : "livraison différée")
                            ->setDateLivraison($dateLivraison)
                            ->setDateSaisie(new \DateTime("now"));
                    $commande->addLivraison($livraison);
        
                    $mouvementProduct = new MouvementProduct();
                    $mouvementProduct->setStockProduct($emplacement)
                                ->setProduct($commande->getProduct())
                                ->setQuantite(-$qtite)
                                ->setLieuVente($lieu_vente)
                                ->setPersonnel($this->getUser())
                                ->setDateOperation($dateLivraison)
                                ->setOrigine("livraison différée")
                                ->setDescription($commentaire ? $commentaire : "livraison différée")
                                ->setClient($commande->getFacturation()->getClient());
                    $livraison->addMouvementProduct($mouvementProduct);
                    $em->persist($livraison);
                }

            }

            $em->flush();
            $this->addFlash("success", 'Livraison éffetuée avec succès :)');
        }

        return $this->redirectToRoute('app_logescom_livraison_livraison_new', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_livraison_livraison_delete', methods: ['POST'])]
    public function delete(Livraison $livraison, LieuxVentes $lieu_vente, StockRepository $stockRep, CommandeProductRepository $commandeProdRep, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livraison->getId(), $request->request->get('_token'))) {
            $emplacement = $livraison->getStock();
            $stock = $stockRep->findOneBy(['product' => $livraison->getCommande()->getProduct(), 'stockProduit' => $emplacement]);

            $stock->setQuantite($stock->getQuantite() + $livraison->getQuantiteLiv());
            $em->persist($stock);

            $commande = $livraison->getCommande();
            $commande->setQuantiteLivre($commande->getQuantiteLivre() - $livraison->getQuantiteLiv());
            $em->persist($commande);

            $etat = 'en-cours';
            $facturation = $livraison->getCommande()->getFacturation();
            $facturation->setEtatLivraison($etat);
            $em->persist($facturation);

            $em->remove($livraison);
            $em->flush();
            $this->addFlash("success", "Livraison annulée avec succès :)");
        }

        return $this->redirectToRoute('app_logescom_livraison_livraison_new', ['id' => $livraison->getCommande()->getFacturation()->getId(), 'lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);

        
    }
}
