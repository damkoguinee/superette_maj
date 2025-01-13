<?php

namespace App\Controller\Logescom\Stock;

use App\Entity\LieuxVentes;
use App\Entity\MouvementProduct;
use App\Entity\TransfertProducts;
use App\Form\TransfertProductsType;
use App\Repository\StockRepository;
use App\Entity\ListeTransfertProduct;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TransfertProductsRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ListeTransfertProductRepository;
use App\Repository\ListeTransfertProductsRepository;
use App\Repository\MouvementProductRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/stock/transfert/products')]
class TransfertProductsController extends AbstractController
{
    #[Route('/index/{lieu_vente}', name: 'app_logescom_stock_transfert_products_index')]
    public function transfertIndex(LieuxVentes $lieu_vente, Request $request, ListeStockRepository $listeStockRep, EntrepriseRepository $entrepriseRep, TransfertProductsRepository $transfertRepo): Response
    {
        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }
        
        $pageEncours = $request->get('pageEncours', 1);
        $pageTransEncours = $request->get('pageTransEncours', 1);

        if ($request->get("magasin")){
            $magasin = $listeStockRep->find($request->get("magasin"));
        }else{
            $magasin = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);

        }

        $liste_transferts = $transfertRepo->findBy(['lieuVenteDepart' => $lieu_vente], ['id' => 'DESC']);

        return $this->render('logescom/stock/transfert_products/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
            'liste_transferts' => $liste_transferts,
            'magasin' => $magasin,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_stock_transfert_products_new')]
    public function transfertNew(LieuxVentes $lieu_vente, Request $request, EntrepriseRepository $entrepriseRep, EntityManagerInterface $em): Response
    {
        $transfert = new TransfertProducts();
        $form = $this->createForm(TransfertProductsType::class, $transfert, ['lieu_vente' => $lieu_vente->getId()] );
        $form->handleRequest($request); 
        
        if ($form->isSubmitted() && $form->isValid()) {
            $transfert->setLieuVenteDepart($lieu_vente)
                    ->setCreePar($this->getUser())
                    ->setEtat("crée")
                    ->setDateEnvoi(new \DateTime("now"));           

            $em->persist($transfert);
            $em->flush();
            $this->addFlash("success", "Transfert ajouté avec succès"); 
            return $this->redirectToRoute('app_logescom_stock_transfert_products_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->render('logescom/stock/transfert_products/new.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'transfert' => $transfert,
            'form'      => $form
            
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_stock_transfert_products_show', methods: ['GET'])]
    public function transfertShow(TransfertProducts $transfert, LieuxVentes $lieu_vente, ListeTransfertProductRepository $listeTransfertRep, MouvementProductRepository $mouvementRep, Request $request, EntrepriseRepository $entrepriseRep): Response
    {
        $liste_transferts = $listeTransfertRep->findBy(['transfert' => $transfert]); 

        $liste_receptions = $mouvementRep->findListeProductsReceptByTransfert($liste_transferts);
        return $this->render('logescom/stock/transfert_products/show.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'transfert_product' => $transfert,
            'liste_transferts'  => $liste_transferts,
            'liste_receptions'  => $liste_receptions,
            'position' => $request->get('position', 'transfert'),
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_stock_transfert_products_edit', methods: ['POST', 'GET'])]
    public function transfertEdit(Request $request, TransfertProducts $transfert, LieuxVentes $lieu_vente, EntityManagerInterface $em, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(TransfertProductsType::class, $transfert, ['lieu_vente' => $lieu_vente->getId()] );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash("success", "Transfert modifié avec succès"); 
            return $this->redirectToRoute('app_logescom_stock_transfert_products_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/stock/transfert_products/edit.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'transfert_product' => $transfert,
            'form'      => $form

        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_stock_transfert_products_delete', methods: ['POST'])]
    public function transfertDelete(Request $request, TransfertProducts $transfert, ListeTransfertProductRepository $listeTransfertRep, StockRepository $stockRep, LieuxVentes $lieu_vente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transfert->getId(), $request->request->get('_token'))) {
            $listeTransferts =  $listeTransfertRep->findBy(['transfert' => $transfert]);

            foreach ($listeTransferts as $listeTransfert) {
                $stock_depart = $stockRep->findOneBy(['stockProduit' => $listeTransfert->getStockDepart(), 'product' => $listeTransfert->getProduct()]);
                // on met à jour le stock de depart. le mouvement product est mis à jour par cascade
                $stock_depart->setQuantite($stock_depart->getQuantite() + $listeTransfert->getQuantite());
                $entityManager->persist($stock_depart);
                // on met à jour le stock de recep. le mouvement product est mis à jour par cascade
                if ($listeTransfert->getStockRecep()) {
                    $stock_recep = $stockRep->findOneBy(['product' => $listeTransfert->getProduct(), 'stockProduit' => $listeTransfert->getStockRecep() ]);
                    $stock_recep->setQuantite($stock_recep->getQuantite() - $listeTransfert->getQuantite());
                    $entityManager->persist($stock_recep);
                }
            }            
            $entityManager->remove($transfert);
            $entityManager->flush();
        }

        $this->addFlash("success", "Transfert supprimé avec succès"); 
        return $this->redirectToRoute('app_logescom_stock_transfert_products_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/transfert/{id}/{lieu_vente}', name: 'app_logescom_stock_transfert_products')]
    public function transfert(TransfertProducts $transfertProduct, LieuxVentes $lieu_vente, LieuxVentesRepository $lieuxVenteRep, Request $request, StockRepository $stockRep, ListeTransfertProductRepository $listeTransfertRep, ListeStockRepository $listeStockRep, EntrepriseRepository $entrepriseRep, EntityManagerInterface $em): Response
    {
        // traitement des transferts
        
        // dd($request->request);
        if ($request->get("transfert") and $request->get("id_stock") and $request->get("quantite") > 0){
            // gestion transfert interne
            if ($transfertProduct->getLieuVenteDepart()->getId() == $transfertProduct->getLieuVenteRecep()->getId()) {             

                $quantite = $request->get("quantite");
                $commentaire = $request->get("commentaire");

                $stock_product_depart = $stockRep->findOneBy(['id' => $request->get("id_stock")]);

                $stock_product_recep = $stockRep->findOneBy(['stockProduit' => $request->get("id_stock_recep"), 'product' => $stock_product_depart->getProducts()]);

                $liste_transfert = new ListeTransfertProduct();
                $liste_transfert->setProduct($stock_product_depart->getProducts())
                        ->setTransfert($transfertProduct)
                        ->setStockDepart($stock_product_depart->getStockProduit())
                        ->setStockRecep($stock_product_recep->getStockProduit())
                        ->setQuantite($quantite)
                        ->setPersonnel($this->getUser())
                        ->setcomentaire($commentaire)
                        ->setDateTransfert(new \DateTime("now"));

                $stock_product_depart->setQuantite($stock_product_depart->getQuantite() - $quantite);

                $stock_product_recep->setQuantite($stock_product_recep->getQuantite() + $quantite);            
            
                // on insère dans le mouvement des produits si quantité different de 0
                if ($quantite != 0) {
                    // on dimunie la qtité dans le mouvement pour le stock de depart
                    $mouvementProductDepart = new MouvementProduct();
                    $mouvementProductDepart->setStockProduct($stock_product_depart->getStockProduit())
                        ->setProduct($stock_product_depart->getProducts())
                        ->setPersonnel($this->getUser())
                        ->setQuantite(-$quantite)
                        ->setLieuVente($stock_product_depart->getStockProduit()->getLieuVente())
                        ->setOrigine("transfert-interne")
                        ->setDescription("transfert-interne")
                        ->setDateOperation(new \DateTime("now"));
                    $liste_transfert->addMouvementProduct($mouvementProductDepart);

                    // on augmente la qtité dans le mouvement pour le stock de reception
                    $mouvementProductRecep = new MouvementProduct();
                    $mouvementProductRecep->setStockProduct($stock_product_recep->getStockProduit())
                        ->setProduct($stock_product_recep->getProducts())
                        ->setPersonnel($this->getUser())
                        ->setQuantite($quantite)
                        ->setLieuVente($stock_product_depart->getStockProduit()->getLieuVente())
                        ->setOrigine("transfert-interne")
                        ->setDescription("transfert-interne")
                        ->setDateOperation(new \DateTime("now"));
                    $liste_transfert->addMouvementProduct($mouvementProductRecep);
                }
                // $transfertProduct->setTraitePar($this->getUser())
                //     ->setEtat("traité");
                // $em->persist($transfertProduct);
                $em->persist($liste_transfert);
                $em->flush(); 
            }else{
                // gestion externe
                $quantite = $request->get("quantite");
                $commentaire = $request->get("commentaire");

                $stock_product_depart = $stockRep->findOneBy(['id' => $request->get("id_stock")]);

                $liste_transfert = new ListeTransfertProduct();
                $liste_transfert->setProduct($stock_product_depart->getProducts())
                        ->setTransfert($transfertProduct)
                        ->setStockDepart($stock_product_depart->getStockProduit())
                        ->setQuantite($quantite)
                        ->setPersonnel($this->getUser())
                        ->setcomentaire($commentaire)
                        ->setDateTransfert(new \DateTime("now"));

                $mouvementProductDepart = new MouvementProduct();
                $mouvementProductDepart->setStockProduct($stock_product_depart->getStockProduit())
                    ->setProduct($stock_product_depart->getProducts())
                    ->setPersonnel($this->getUser())
                    ->setQuantite(-$quantite)
                    ->setLieuVente($stock_product_depart->getStockProduit()->getLieuVente())
                    ->setOrigine("transfert-externe")
                    ->setDescription("transfert-externe")
                    ->setDateOperation(new \DateTime("now"));
                $liste_transfert->addMouvementProduct($mouvementProductDepart);

                $stock_product_depart->setQuantite($stock_product_depart->getQuantite() - $quantite);

                $em->persist($liste_transfert);
                $em->flush();                
            }
            return new RedirectResponse($this->generateUrl('app_logescom_stock_transfert_products', ['id' => $transfertProduct->getId(), 'lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)])); 
        }
        
        
        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }
        
        $pageEncours = $request->get('pageEncours', 1);
        $pageTransEncours = $request->get('pageTransEncours', 1);

        if ($request->get("magasin")){
            $magasin = $listeStockRep->find($request->get("magasin"));
        }else{
            $magasin = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);

        }

        $stocks = $stockRep->findStocksForTransfertPaginated($magasin, $search, $pageEncours, 5); 

        $liste_transferts = $listeTransfertRep->findListeProductsForTransfertPaginated($transfertProduct, $search, $pageTransEncours, 20); 

        $listeStocks = $listeStockRep->findBy(['lieuVente' => $lieu_vente]);
        $liste_lieux_vente = $lieuxVenteRep->findAllLieuxVenteExecept($lieu_vente);
        return $this->render('logescom/stock/transfert_products/transfert.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
            'liste_stocks' => $listeStocks,
            'liste_transferts' => $liste_transferts,
            'magasin' => $magasin,
            'liste_lieux_vente' => $liste_lieux_vente,
            'stocks' => $stocks,
            'transfert' => $transfertProduct,
        ]);
    }


    
}
