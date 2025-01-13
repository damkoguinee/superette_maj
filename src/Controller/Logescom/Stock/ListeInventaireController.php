<?php

namespace App\Controller\Logescom\Stock;

use App\Entity\Inventaire;
use App\Entity\LieuxVentes;
use Doctrine\ORM\Query\Expr;
use App\Entity\AnomalieProduit;
use App\Entity\ListeInventaire;
use App\Entity\MouvementProduct;
use App\Form\ListeInventaireType;
use App\Repository\StockRepository;
use App\Repository\ProductsRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\InventaireRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AnomalieProduitRepository;
use App\Repository\ListeInventaireRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MouvementProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/stock/liste/inventaire')]
class ListeInventaireController extends AbstractController
{
    #[Route('/', name: 'app_logescom_stock_liste_inventaire_index', methods: ['GET', 'POST'])]
    public function index(ListeInventaireRepository $listeInventaireRepository, EntrepriseRepository $entrepriseRep): Response
    {  
        return $this->render('logescom/stock/liste_inventaire/index.html.twig', [
            'liste_inventaires' => $listeInventaireRepository->findAll(),
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_stock_liste_inventaire_new', methods: ['GET', 'POST'])]
    public function new(LieuxVentes $lieu_vente, Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep, ListeInventaireRepository $listeInventaireRepository, ListeStockRepository $listeStockRep): Response
    {
        $listeInventaire = new ListeInventaire();
        $form = $this->createForm(ListeInventaireType::class, $listeInventaire);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $listeInventaire->setPersonnel($this->getUser());
            $listeInventaire->setLieuVente($lieu_vente);
            $listeInventaire->setDateCreation(new \DateTime("now"));
            $entityManager->persist($listeInventaire);
            $entityManager->flush();
            $listeInventaire = new ListeInventaire();
            $form = $this->createForm(ListeInventaireType::class, $listeInventaire); // pour initialiser
            $this->addFlash("success", "Inventaire crée avec succès");
            return new RedirectResponse($this->generateUrl('app_logescom_stock_liste_inventaire_new', ['lieu_vente' => $lieu_vente->getId()]));
        }
        
        return $this->render('logescom/stock/liste_inventaire/index.html.twig', [
            'liste_inventaire' => $listeInventaire,
            'form' => $form,
            'lieu_vente' => $lieu_vente,
            'entreprise' => $entrepriseRep->find(1),
            'liste_stock' => $listeStockRep->findBy(['lieuVente' => $lieu_vente]),
            'liste_inventaires' => $listeInventaireRepository->findBy(['lieuVente' => $lieu_vente]),
        ]);
    }

    #[Route('/show/{id}', name: 'app_logescom_stock_liste_inventaire_show', methods: ['GET'])]
    public function show(ListeInventaire $listeInventaire, InventaireRepository $inventaireRep, EntrepriseRepository $entrepriseRep): Response
    {
        $inventaires = $inventaireRep->findBy(['inventaire' => $listeInventaire], ['product' => 'ASC']);
        return $this->render('logescom/stock/liste_inventaire/show.html.twig', [
            'liste_inventaire' => $listeInventaire,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $listeInventaire->getLieuVente(),
            'inventaires' => $inventaires
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_stock_liste_inventaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ListeInventaire $listeInventaire, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(ListeInventaireType::class, $listeInventaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_stock_liste_inventaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/stock/liste_inventaire/edit.html.twig', [
            'liste_inventaire' => $listeInventaire,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $listeInventaire->getLieuVente(),

        ]);
    }

    #[Route('/delete/{id}', name: 'app_logescom_stock_liste_inventaire_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, ListeInventaire $listeInventaire, InventaireRepository $inventaireRep, MouvementProductRepository $mouvementProductRep, StockRepository $stockRep,  EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$listeInventaire->getId(), $request->request->get('_token'))) {
            $inventaires = $inventaireRep->findBy(['inventaire' => $listeInventaire, 'statut' => 'clos']);
            foreach ($inventaires as $value) {
                $mouvementProduct = $mouvementProductRep->findOneBy(['inventaire' => $value->getId()]);
                $stock = $stockRep->findOneBy(['product' => $value->getProduct(), 'stockProduit' => $value->getStock() ]);
                $stock->setQuantite($stock->getQuantite() + $value->getEcart());
                $entityManager->persist($stock);
                if ($mouvementProduct) {
                    $entityManager->remove($mouvementProduct);
                }
                $entityManager->remove($value);
            }
            $entityManager->remove($listeInventaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_stock_liste_inventaire_new', ['lieu_vente' => $listeInventaire->getLieuVente()->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/inventaire/{id}', name: 'app_logescom_stock_inventaire_index', methods: ['GET', 'POST'])]
    public function inventaire(ListeInventaire $listeInventaire, ListeStockRepository $listeStockRep, InventaireRepository $inventaireRep, EntrepriseRepository $entrepriseRep, StockRepository $stockRep, ProductsRepository $productsRep, MouvementProductRepository $mouvementProductRep, AnomalieProduitRepository $anomalieRep, Request $request, EntityManagerInterface $em): Response
    {
        /**
         * Zone ajustement partiel du stock
         */        
        if ($request->get("ajustement") and $request->get("id_inv")){
            $mouvementProduct = new MouvementProduct();
            $anomalie = new AnomalieProduit();

            $inventaire = $inventaireRep->findOneBy(['id'=> $request->get("id_inv")]);

            $products = $productsRep->find($inventaire->getProduct());

            $stocksEntite = $listeStockRep->find($inventaire->getStock());

            $ecart = $inventaire->getEcart();
            $quantite_init = $request->get('quantite_init') ? $request->get('quantite_init') : 0 ;
            $stockProduct = $stockRep->findOneBy(['product' => $products, 'stockProduit' => $stocksEntite]);
            if ($request->get("ajustement") == "partiel" and $inventaire->getStatut() != "clos" ) {
                // on met à jour le stock
                $qtite_stock_init = $stockProduct->getQuantite() ? $stockProduct->getQuantite() : 0;
                $stockProduct->setQuantite($inventaire->getQuantiteInv());

                $mouvement_actuel = $mouvementProductRep->totalQuantiteParProduitParStock($products, $inventaire->getStock());

                $difference = $inventaire->getQuantiteInv() - $mouvement_actuel;

                // on insère dans le mouvement des produits
                $mouvementProduct->setStockProduct($stocksEntite)
                    ->setProduct($products)
                    ->setPersonnel($this->getUser())
                    ->setQuantite($difference)
                    ->setLieuVente($stocksEntite->getLieuVente())
                    ->setOrigine("inventaire")
                    ->setDescription("inventaire des produits")
                    ->setInventaire($inventaire)
                    ->setDateOperation(new \DateTime("now"));

                // on insere dans l'anomalie

                $anomalie->setStock($stocksEntite)
                    ->setProduct($products)
                    ->setPersonnel($this->getUser())
                    ->setInventaire($inventaire)
                    ->setQuantite($inventaire->getEcart())
                    ->setPrixRevient($stockProduct->getPrixRevient())
                    ->setDateAnomalie(new \DateTime("now"));

                // on met à jour l'inventaire
                $inventaire->setStatut("clos");
                $em->persist($mouvementProduct);
                $em->persist($anomalie);
                $em->persist($stockProduct);
                $em->persist($inventaire);
                $em->flush();

                // $this->addFlash("success", "opération éffectuée avec succès 😊");
                // return new RedirectResponse($this->generateUrl('app_logescom_stock_inventaire_index', ['id' => $listeInventaire->getId()]));
            }
            if ($request->get("ajustement") == "deletePartiel" and $inventaire->getStatut() == "clos" ) {
                // on met à jour le stock
                $qtite_stock_init = $stockProduct->getQuantite() ? $stockProduct->getQuantite() : 0;
                $stockProduct->setQuantite($qtite_stock_init + $inventaire->getEcart());
              
                // on recupere le mouvement de sctock
                $mouvementAnnules = $mouvementProductRep->findBy(['inventaire' => $inventaire]);
                // $mouvementAnnule->setQuantite(0)
                //                 ->setDescription("inventaire annulé");

                // on recupere l'anomalie 
                $anomalie = $anomalieRep->findOneBy(['inventaire' => $inventaire]);

                // on met à jour l'inventaire
                $inventaire->setStatut("en-cours");
                foreach ($mouvementAnnules as $mouvementAnnule) {
                    $em->remove($mouvementAnnule);
                }
                $em->persist($stockProduct);
                $em->persist($inventaire);
                $em->remove($anomalie);
                $em->flush();

                // $this->addFlash("success", "opération éffectuée avec succès 😊");
                // return new RedirectResponse($this->generateUrl('app_logescom_stock_inventaire_index', ['id' => $listeInventaire->getId()]));
            }
        }

        // zone insertion de l'inventaire
        if ($request->get("quantite") !== null){
            $inventaire = new Inventaire();
            $products = $productsRep->find($request->get("id_product"));
            $stocksEntite = $listeStockRep->find($request->get('magasin'));
            $quantite_init = $request->get('quantite_init') ? $request->get('quantite_init') : 0 ;
            $inventaire->setInventaire($listeInventaire)
                ->setProduct($products)
                ->setStock($stocksEntite)
                ->setPersonnel($this->getUser())
                ->setQuantiteInit($quantite_init)
                ->setQuantiteInv($request->get('quantite'))
                ->setEcart($quantite_init - $request->get('quantite'))
                ->setStatut('en-cours')
                ->setDateInventaire(new \DateTime("now"));

                if ($request->get('id_inv')) {
                    $verif_inventaire = $inventaireRep->find($request->get('id_inv'));
                    if ($verif_inventaire) {
                        $em->remove($verif_inventaire);
                    }
                }
            $em->persist($inventaire);
            $em->flush(); 
            
            $inventaire = new Inventaire();
            return new RedirectResponse($this->generateUrl('app_logescom_stock_inventaire_index', ['id' => $listeInventaire->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)]));            
        }

        // Zone rendu twig        
        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }
        
        $pageEncours = $request->get('pageEncours', 1);

        if ($request->get("magasin")){
            $magasin = $listeStockRep->find($request->get("magasin"));
        }else{
            $magasin = $listeStockRep->findOneBy(['lieuVente' => $listeInventaire->getLieuVente()]);

        }

        $listeStocks = $listeStockRep->findBy(['lieuVente' => $listeInventaire->getLieuVente()]);

        $liste_produits_stock = $stockRep->findStocksForApproInitPaginated($magasin, $search, $pageEncours, 50);
        $stock_inventaire = [];
        foreach ($liste_produits_stock['data'] as $produit_stock) {
            $inventaire_produit = $inventaireRep->findOneBy(['inventaire' => $listeInventaire, 'product' => $produit_stock->getProducts(), 'stock' => $produit_stock->getStockProduit()]);            
            
            $stock_inventaire[] = [
                'inventaire' => $inventaire_produit,
                'produit' => $produit_stock
            ];
            
        }
        

        // $stocks = $stockRep->findProductsPaginated($listeInventaire, $magasin, $search, $pageEncours, 50); 
       
        return $this->render('logescom/stock/inventaire/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $listeInventaire->getLieuVente(),
            'liste_stocks' => $listeStocks,
            'magasin' => $magasin,
            'search' => $search,
            'inventaire' => $listeInventaire,
            'stocks' => $stock_inventaire,
            'pagination' => $liste_produits_stock,
        ]);
    }

    #[Route('/cloturess/{id}', name: 'app_logescom_stock_liste_inventaire_cloture', methods: ['POST', 'GET'])]
    public function clotureInventaire(Request $request, ListeInventaire $listeInventaire, InventaireRepository $inventaireRep, MouvementProductRepository $mouvementProductRep, StockRepository $stockRep,  EntityManagerInterface $entityManager): Response
    {
        $expr = new Expr();        
        $inventaires = $inventaireRep->findBy([
            'inventaire' => $listeInventaire, 
            'statut' => 'en-cours'
            // $expr->neq('ecart', 0) // 'ecart' doit être différent de 0
        ]);

        foreach ($inventaires as $inventaire) {
            if ($inventaire->getEcart() != null ) {
                $mouvementProduct = new MouvementProduct();
                $stock = $stockRep->findOneBy(['product' => $inventaire->getProduct(), 'stockProduit' => $inventaire->getStock() ]);
                // on met à jour le stock
                $stock->setQuantite($stock->getQuantite() + (-$inventaire->getEcart()));

                $mouvement_actuel = $mouvementProductRep->totalQuantiteParProduitParStock($inventaire->getProduct(), $inventaire->getStock());

                $difference = $inventaire->getQuantiteInv() - $mouvement_actuel;
                // on insère dans le mouvement des produits
                $mouvementProduct->setStockProduct($inventaire->getStock())
                ->setProduct($inventaire->getProduct())
                ->setPersonnel($this->getUser())
                ->setQuantite($difference)
                ->setLieuVente($inventaire->getStock()->getLieuVente())
                ->setOrigine("inventaire")
                ->setDescription("inventaire des produits")
                ->setInventaire($inventaire)
                ->setDateOperation(new \DateTime("now"));

                // on met à jour la table anomalie

                $anomalie = new AnomalieProduit();
                $anomalie->setStock($inventaire->getStock())
                    ->setProduct($inventaire->getProduct())
                    ->setPersonnel($this->getUser())
                    ->setInventaire($inventaire)
                    ->setQuantite($inventaire->getEcart())
                    ->setPrixRevient($stock->getPrixRevient())
                    ->setDateAnomalie(new \DateTime("now"));

                // on met à jour l'inventaire
                $inventaire->setStatut("clos");

                $entityManager->persist($anomalie);
                $entityManager->persist($mouvementProduct);
                $entityManager->persist($stock);
                $entityManager->persist($inventaire);
            }
        }
        $entityManager->flush();
        

        return $this->redirectToRoute('app_logescom_stock_liste_inventaire_new', ['lieu_vente' => $listeInventaire->getLieuVente()->getId()], Response::HTTP_SEE_OTHER);
    }

}
