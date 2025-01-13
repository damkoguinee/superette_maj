<?php

namespace App\Controller\Logescom\Stock;

use App\Entity\Stock;
use App\Entity\LieuxVentes;
use App\Entity\AnomalieProduit;
use App\Entity\MouvementProduct;
use App\Entity\SortieStock;
use App\Form\AnomalieProduitType;
use App\Repository\StockRepository;
use App\Repository\ProductsRepository;
use App\Repository\LivraisonRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AnomalieProduitRepository;
use App\Repository\CommandeProductRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MouvementProductRepository;
use App\Repository\SortieStockRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/stock/gestion/stock')]
class GestionStockController extends AbstractController
{
    #[Route('/{lieu_vente}', name: 'app_logescom_stock_gestion_stock_index')]
    public function index(LieuxVentes $lieu_vente, Request $request, ListeStockRepository $listeStockRep, StockRepository $stockRep,  EntrepriseRepository $entrepriseRep, EntityManagerInterface $em): Response
    {
        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }
        
        $pageEncours = $request->get('pageEncours', 1);

        if ($request->get("magasin")){
            $magasin = $listeStockRep->find($request->get("magasin"));
        }else{
            $magasin = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);

        }

        // partie ajusteent prix de vente des produits

        if ($request->get("ajustement") and $request->get("id_stock")){
            $prixVente = $request->get("prix_vente");

            if (is_numeric($prixVente) && $prixVente >= 0) {
                $stock_product = $stockRep->find($request->get("id_stock"));
                if ($request->get("peremption")) {
                    $stock_product->setDatePeremption(new \DateTime($request->get("peremption")));
                }
                $stock_product->setPrixVente($prixVente);

                $em->persist($stock_product);
                $em->flush();

                return new RedirectResponse($this->generateUrl('app_logescom_stock_gestion_stock_index', ['lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)])); 
            }
            

        }
        $stocks = $stockRep->findStocksPaginated($magasin, $search, $pageEncours, 100); 
        


        $listeStocks = $listeStockRep->findBy(['lieuVente' => $lieu_vente]);
        return $this->render('logescom/stock/gestion_stock/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'liste_stocks' => $listeStocks,
            'magasin' => $magasin,
            'search' => $search,
            'stocks' => $stocks,

        ]);
    }

    #[Route('/detail/{stock}', name: 'app_logescom_stock_gestion_stock_show')]
    public function show(Stock $stock, AnomalieProduitRepository $anomalieRep, Request $request, EntrepriseRepository $entrepriseRep, EntityManagerInterface $em): Response
    {
        $anomalieProduit = new AnomalieProduit();
        $form = $this->createForm(AnomalieProduitType::class, $anomalieProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anomalieProduit->setStock($stock->getStockProduit())
                ->setProduct($stock->getProducts())
                ->setPrixRevient($stock->getPrixRevient())
                ->setPersonnel($this->getUser())
                ->setDateAnomalie(new \DateTime("now"));

            // on met à jour le produit dans le stock

            $stock->setQuantite($stock->getQuantite() + $anomalieProduit->getQuantite());

            // on insère dans le mouvement des produits
            $mouvementProduct = new MouvementProduct();
            $mouvementProduct->setStockProduct($stock->getStockProduit())
                ->setProduct($stock->getProducts())
                ->setPersonnel($this->getUser())
                ->setQuantite($anomalieProduit->getQuantite())
                ->setLieuVente($stock->getStockProduit()->getLieuVente())
                ->setOrigine("stock")
                ->setDescription($anomalieProduit->getComentaire())
                ->setDateOperation(new \DateTime("now"));
            $anomalieProduit->addMouvementProduct($mouvementProduct);

            $em->persist($anomalieProduit);
            $em->persist($stock);
            $em->persist($mouvementProduct);
            $em->flush();

            $this->addFlash("success", "L'anomalie a été ajoutée avec succès. 😢");
            
            return new RedirectResponse($this->generateUrl('app_logescom_stock_gestion_stock_show', ['stock' => $stock->getId()]));
        }
        
        return $this->render('logescom/stock/gestion_stock/show.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $stock->getStockProduit()->getLieuVente(),
            'stock' => $stock,
            'anomalie_produit' => $anomalieProduit,
            'liste_anomalies' => $anomalieRep->findBy(['stock' => $stock->getStockProduit(), 'product' => $stock->getProducts()], ['dateAnomalie' => 'DESC', 'stock' => 'ASC']),

            'form' => $form,

        ]);

    }


    #[Route('/anomalie/{lieu_vente}', name: 'app_logescom_stock_gestion_stock_anomalie')]
    public function anomalieStock(LieuxVentes $lieu_vente, Request $request, ListeStockRepository $listeStockRep, StockRepository $stockRep, MouvementProductRepository $mouvementProductRep, EntrepriseRepository $entrepriseRep, EntityManagerInterface $em): Response
    {
        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }
        
        $pageEncours = $request->get('pageEncours', 1);

        if ($request->get("magasin")){
            $magasin = $listeStockRep->find($request->get("magasin"));
        }else{
            $magasin = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);

        }

        // partie ajustement
        if ($request->get("ajustement") and $request->get("id_stock")){
            $qtite_reel = $request->get("qtite_reel");
            $qtite_mouv = $request->get('qtite_mouv');

            $stock_product = $stockRep->find($request->get("id_stock"));

            $stock_product->setQuantite($qtite_reel);

            $mouvement = new MouvementProduct();
            if ($qtite_mouv != $qtite_reel) {
                $qtite_reel = $qtite_reel ? $qtite_reel : 0;
                $qtite_mouv = $qtite_mouv ? $qtite_mouv : 0;
                $difference = $qtite_reel - $qtite_mouv;

                $mouvement->setStockProduct($stock_product->getStockProduit())
                        ->setProduct($stock_product->getProducts())
                        ->setQuantite($difference)
                        ->setLieuVente($lieu_vente)
                        ->setPersonnel($this->getUser())
                        ->setDateOperation(new \DateTime("now"))
                        ->setOrigine("anomalie stock")
                        ->setDescription("ecart entre le mouv et le stock");
                    
                $em->persist($mouvement);
            }

            $em->persist($stock_product);
            $em->flush();

            return new RedirectResponse($this->generateUrl('app_logescom_stock_gestion_stock_anomalie', ['lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)])); 
            
            

        }
        $anomalies = [];
        $stocks = $stockRep->listeStockGeneralParMagasinParRecherchePagination($magasin, $search, $pageEncours, 2000); 
        foreach ($stocks['data'] as $stock) {
            $mouvement = $mouvementProductRep->totalQuantiteParProduitParStock($stock->getProducts(), $stock->getStockProduit());

            if ($stock->getQuantite() != $mouvement) {
                $anomalies [] = [
                    'stock' => $stock,
                    'mouvement' => $mouvement,
                ];
            }            
        }

        $listeStocks = $listeStockRep->findBy(['lieuVente' => $lieu_vente]);
        return $this->render('logescom/stock/gestion_stock/anomalie.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'liste_stocks' => $listeStocks,
            'magasin' => $magasin,
            'search' => $search,
            'anomalies' => $anomalies,

        ]);
    }


    #[Route('/appro/initial/{lieu_vente}', name: 'app_logescom_stock_gestion_stock_appro_initial')]
    public function approInitial(LieuxVentes $lieu_vente, Request $request, ListeStockRepository $listeStockRep, StockRepository $stockRep,  EntrepriseRepository $entrepriseRep, MouvementProductRepository $mouvementProductRep, EntityManagerInterface $em): Response
    {
        // partie ajustement prix de vente des produits
        if ($request->get("ajustement") and $request->get("id_stock")){
            if ($request->get("quantite")) {
                $quantite = $request->get("quantite");
            }else{
                $quantite = 0;
            }
            $prix_achat = str_replace(' ', '', $request->get("prix_achat"));
            $prix_vente = str_replace(' ', '', $request->get("prix_vente"));
            $prix_revient = str_replace(' ', '', $request->get("prix_revient"));
            $peremption = str_replace(' ', '', $request->get("peremption"));
            $designation = $request->get("designation");

            
            $stock_product = $stockRep->find($request->get("id_stock"));

            if (($quantite + $stock_product->getQuantite())) {
                $prix_revient_moyen =(($prix_revient * $quantite + $stock_product->getPrixRevient() * $stock_product->getQuantite()) / ($quantite + $stock_product->getQuantite()));

                $stock_product->setPrixRevient($prix_revient_moyen);
            }

            $stock_product->setPrixVente($prix_vente)
                ->setQuantite($stock_product->getQuantite() + $quantite)
                ->setPrixAchat($prix_achat);

            $product_maj = $stock_product->getProducts();
            $product_maj->setDesignation($designation); 
            

            if ($peremption) {
                $stock_product->setDatePeremption($peremption ? (new \DateTime($peremption)) : '');
            }
                   

            // on insère dans le mouvement des produits si quantité different de 0
            if ($quantite != 0) {
                $mouvementProduct = new MouvementProduct();
                $mouvementProduct->setStockProduct($stock_product->getStockProduit())
                    ->setProduct($stock_product->getProducts())
                    ->setPersonnel($this->getUser())
                    ->setQuantite($quantite)
                    ->setLieuVente($stock_product->getStockProduit()->getLieuVente())
                    ->setOrigine("appro-initial")
                    ->setDescription("appro-initial")
                    ->setDateOperation(new \DateTime("now"));
                $em->persist($mouvementProduct);
            }
            $em->persist($stock_product);
            $em->persist($product_maj);
            $em->flush(); 

            return new RedirectResponse($this->generateUrl('app_logescom_stock_gestion_stock_appro_initial', ['lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)]));           

        }
        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }
        
        $pageEncours = $request->get('pageEncours', 1);
        $pageMouvEncours = $request->get('pageMouvEncours', 1);

        if ($request->get("magasin")){
            $magasin = $listeStockRep->find($request->get("magasin"));
        }else{
            $magasin = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);

        }

        if ($search) {
            $stocks = $stockRep->findStocksByCodeBarrePaginated($magasin, $search, 1, 1);  
        }
        if (empty($stocks['data'])) {  
            $stocks = $stockRep->findStocksForApproInitPaginated($magasin, $search, $pageEncours, 5); 
        } 

        $liste_appros_initial = $mouvementProductRep->findListeProductsForApproInitPaginated('appro-initial', $magasin, $search, $pageMouvEncours, 20); 

        $listeStocks = $listeStockRep->findBy(['lieuVente' => $lieu_vente]);
        return $this->render('logescom/stock/gestion_stock/appro_initial.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'liste_stocks' => $listeStocks,
            'liste_appros_initial' => $liste_appros_initial,
            'magasin' => $magasin,
            'search' => $search,
            'stocks' => $stocks,

        ]);
    }

    #[Route('/delete/appro/initial/{id}', name: 'app_logescom_stock_gestion_stock_appro_initial_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, MouvementProduct $mouvementProduct, StockRepository $stockRep, EntityManagerInterface $entityManager): Response
    {
        $stock = $stockRep->findOneBy(['product' => $mouvementProduct->getProduct(), 'stockProduit' => $mouvementProduct->getStockProduct() ]);

        $stock->setQuantite($stock->getQuantite() - $mouvementProduct->getQuantite());
        $entityManager->persist($stock);
        $entityManager->remove($mouvementProduct);
        $entityManager->flush();
        $this->addFlash("success", "appro-initial supprimé avec succès ");
        return $this->redirectToRoute('app_logescom_stock_gestion_stock_appro_initial', ['lieu_vente' => $mouvementProduct->getLieuVente()->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/sortie/{lieu_vente}', name: 'app_logescom_stock_gestion_stock_sortie')]
    public function sortieStock(LieuxVentes $lieu_vente, SortieStockRepository $sortieRep, Request $request, EntrepriseRepository $entrepriseRep, StockRepository $stockRep, ProductsRepository $productRep, ListeStockRepository $listeStockRep,  CommandeProductRepository $commandeProdRep, LivraisonRepository $livraisonRep, EntityManagerInterface $em): Response
    {
        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }

        if ($request->get('valider')) {
            
            $qtite =  floatval(preg_replace('/[^0-9,.]/', '', $request->get('qtite')));
            $commentaire = $request->get('commentaire');
            $date = $request->get('date').' '.date('H:i');
            $date = new \DateTime($date);

            $id_product = $request->get('id_product');
            $product = $productRep->find($id_product);

            $id_stock = $request->get('stock');
            $listeStock = $listeStockRep->find($id_stock);
            $stock = $stockRep->findOneBy(['product' => $product, 'stockProduit' => $listeStock]);
            $qtite_dispo = $stock->getQuantite();

            $stock->setQuantite($stock->getQuantite() - $qtite);
            $em->persist($stock);

            $sortieStock = new SortieStock();
            $sortieStock->setProduct($product)
                    ->setStock($listeStock)
                    ->setQuantite($qtite)
                    ->setCommentaire($commentaire)
                    ->setSaisiePar($this->getUser())
                    ->setDateSaisie(new \DateTime("now"))
                    ->setDateOperation($date);

            $mouvementProduct = new MouvementProduct();
            $mouvementProduct->setStockProduct($listeStock)
                        ->setProduct($product)
                        ->setQuantite(-$qtite)
                        ->setLieuVente($lieu_vente)
                        ->setPersonnel($this->getUser())
                        ->setDateOperation($date)
                        ->setOrigine("sortie stock")
                        ->setDescription($commentaire ? $commentaire : "sortie stock");
            $sortieStock->addMouvementProduct($mouvementProduct);

            $em->persist($sortieStock);
            $em->persist($mouvementProduct);
            $em->flush();
            $this->addFlash('success', 'sortie enregistrée avec succès :)');

            return $this->redirectToRoute('app_logescom_stock_gestion_stock_sortie', ['lieu_vente' => $lieu_vente->getId(), 'search' => $search], Response::HTTP_SEE_OTHER);
        }
        
        
        $pageEncours = $request->get('pageEncours', 1);
        $pageTransEncours = $request->get('pageTransEncours', 1);

        if ($request->get("magasin")){
            $magasin = $listeStockRep->find($request->get("magasin"));
        }else{
            $magasin = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);

        }

        $products = $productRep->findProductsForPaginated($search, $pageEncours, 20); 

        $data = [];
        foreach ($products['data'] as $value) {
            $stocks = $stockRep->sumQuantiteByProductByLieu($value , $lieu_vente);
            $dispo = $stockRep->disponibiliteProduitParLieu($value, $lieu_vente);
            
            if ($dispo) {
                $data[] = [
                    'product' => $value,
                    'stocks' => $stocks
                ];
            }

        }
        $sorties = $sortieRep->findBy([], ['id' => 'DESC']);
        return $this->render('logescom/stock/gestion_stock/sortie_stock.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'data' => $data,
            'sorties' => $sorties,
            'listeStocks' => $listeStockRep->findBy(['lieuVente' => $lieu_vente]),
        ]);
    }


    #[Route('/delete/sortie/{id}/{lieu_vente}', name: 'app_logescom_stock_gestion_stock_sortie_delete', methods: ['POST', 'GET'])]
    public function deleteSortie(Request $request, SortieStock $sortieStock, StockRepository $stockRep, LieuxVentes $lieu_vente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortieStock->getId(), $request->request->get('_token'))) {

            $stock = $stockRep->findOneBy(['product' => $sortieStock->getProduct(), 'stockProduit' => $sortieStock->getStock()]);

            $stock->setQuantite($stock->getQuantite() + $sortieStock->getQuantite());
            $entityManager->persist($stock);            
            $entityManager->remove($sortieStock);
            $entityManager->flush();
        }
        $this->addFlash("success", "sortie supprimé avec succès ");
        return $this->redirectToRoute('app_logescom_stock_gestion_stock_sortie', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);

    }
}
