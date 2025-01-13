<?php

namespace App\Controller\Logescom\Stock;

use App\Entity\LieuxVentes;
use App\Entity\MouvementProduct;
use App\Entity\TransfertProduct;
use App\Entity\TransfertProducts;
use App\Form\TransfertProductType;
use App\Repository\StockRepository;
use App\Entity\ListeTransfertProduct;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\TransfertProductRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TransfertProductsRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ListeTransfertProductRepository;
use App\Repository\MouvementProductRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/stock/liste/transfert/product')]
class ListeTransfertProductController extends AbstractController
{
    #[Route('/index/{lieu_vente}', name: 'app_logescom_stock_Liste_transfert_products_index')]
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

        $liste_transferts = $transfertRepo->findBy(['lieuVenteRecep' => $lieu_vente], ['id' => 'DESC']);

        return $this->render('logescom/stock/liste_transfert_product/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
            'liste_transferts' => $liste_transferts,
            'magasin' => $magasin,
        ]);
    }

    #[Route('/transfert/{id}/{lieu_vente}', name: 'app_logescom_stock_liste_transfert_products_reception')]
    public function transfert(TransfertProducts $transfertProduct, LieuxVentes $lieu_vente,  Request $request, StockRepository $stockRep, ListeTransfertProductRepository $listeTransfertRep, ListeStockRepository $listeStockRep, MouvementProductRepository $mouvementRep, EntrepriseRepository $entrepriseRep, EntityManagerInterface $em): Response
    {
        // traitement des receptions
        if ($request->get("reception") and $request->get("id_liste_transfert") and $request->get("quantite") > 0){
            $quantite = $request->get("quantite");
            $liste_transfert = $listeTransfertRep->find($request->get("id_liste_transfert"));
            $stock_product_recep = $stockRep->findOneBy(['stockProduit' => $request->get("id_stock_recep"), 'product' => $liste_transfert->getProduct()]);
            $liste_transfert->setStockRecep($stock_product_recep->getStockProduit())
                        ->setQuantiteRecep($liste_transfert->getQuantiteRecep() + $quantite)
                        ->setDateReception(new \DateTime("now"));
            $stock_product_recep->setQuantite($stock_product_recep->getQuantite() + $quantite); 
            // on augmente la qtité dans le mouvement pour le stock de reception
            $mouvementProductRecep = new MouvementProduct();
            $mouvementProductRecep->setStockProduct($stock_product_recep->getStockProduit())
                ->setProduct($stock_product_recep->getProducts())
                ->setPersonnel($this->getUser())
                ->setQuantite($quantite)
                ->setLieuVente($stock_product_recep->getStockProduit()->getLieuVente())
                ->setOrigine("transfert-externe")
                ->setDescription("transfert-externe")
                ->setDateOperation(new \DateTime("now"));
            $liste_transfert->addMouvementProduct($mouvementProductRecep);
            $transfertProduct->setTraitePar($this->getUser())
                            ->setDateReception(new \DateTime("now"));
            // on vérifie s'il manque des produits à receptionner si oui on change l'Etat
            $liste_products = $listeTransfertRep->findBy(['transfert' => $transfertProduct]);
            $etat = 'clos';
            foreach ($liste_products as  $value) {
                if ($value->getQuantite() != $value->getQuantiteRecep()) {
                    $etat = 'en-cours';
                    break;
                }
            }
            $transfertProduct->setEtat($etat);

            $em->persist($liste_transfert);
            $em->persist($mouvementProductRecep);
            $em->persist($transfertProduct);
            $em->flush(); 
            
            return new RedirectResponse($this->generateUrl('app_logescom_stock_liste_transfert_products_reception', ['id' => $transfertProduct->getId(), 'lieu_vente' => $lieu_vente->getId()])); 
        }

        $products_reception = $listeTransfertRep->findBy(['transfert' => $transfertProduct]);
        $liste_receptions = $mouvementRep->findListeProductsReceptByTransfert($products_reception);
        $listeStocks = $listeStockRep->findBy(['lieuVente' => $lieu_vente]);

        return $this->render('logescom/stock/liste_transfert_product/reception.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'liste_stocks' => $listeStocks,
            'products_reception' => $products_reception,
            'transfert' => $transfertProduct,
            'liste_receptions' => $liste_receptions,
        ]);
    }



    

    #[Route('/delete/transfert/{id}', name: 'app_logescom_stock_liste_transfert_product_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, ListeTransfertProduct $transfertProduct, StockRepository $stockRep, EntityManagerInterface $entityManager): Response
    {
        $stock_depart = $stockRep->findOneBy(['product' => $transfertProduct->getProduct(), 'stockProduit' => $transfertProduct->getStockDepart() ]);
        $stock_depart->setQuantite($stock_depart->getQuantite() + $transfertProduct->getQuantite());
        $entityManager->persist($stock_depart);

        if ($transfertProduct->getStockRecep()) {
            $stock_recep = $stockRep->findOneBy(['product' => $transfertProduct->getProduct(), 'stockProduit' => $transfertProduct->getStockRecep() ]);
            $stock_recep->setQuantite($stock_recep->getQuantite() - $transfertProduct->getQuantite());
            $entityManager->persist($stock_recep);
        }

        $entityManager->remove($transfertProduct);
        $entityManager->flush();

        $this->addFlash("success", "le transfert a été supprimé avec succès ");
        return $this->redirectToRoute('app_logescom_stock_transfert_products', ['id' => $transfertProduct->getTransfert()->getId(), 'lieu_vente' => $transfertProduct->getStockDepart()->getLieuVente()->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete/reception/{id}/{lieu_vente}', name: 'app_logescom_stock_liste_transfert_product_delete_reception', methods: ['POST', 'GET'])]
    public function deleteReception(Request $request, MouvementProduct $mouvementProduct, LieuxVentes $lieu_vente, StockRepository $stockRep, EntityManagerInterface $entityManager): Response
    {
        $stock_recep = $stockRep->findOneBy(['product' => $mouvementProduct->getProduct(), 'stockProduit' => $mouvementProduct->getStockProduct() ]);
        // on incremente la qtite du stock
        $stock_recep->setQuantite($stock_recep->getQuantite() - $mouvementProduct->getQuantite());
        // on decrement la qtite reception dans liste des transferts
        $liste_product = $mouvementProduct->getTransfert();
        $liste_product->setQuantiteRecep($mouvementProduct->getTransfert()->getQuantiteRecep() - $mouvementProduct->getQuantite())
                ->setDateReception(new \DateTime("now"));

        // on actualise le transfert product
        $transfert_product = $liste_product->getTransfert();
        $transfert_product->setEtat('en-cours')
                        ->setDateReception(new \DateTime("now"))
                        ->setTraitePar($this->getUser());
                        
        $entityManager->persist($transfert_product);
        $entityManager->persist($liste_product);
        $entityManager->persist($stock_recep);
        $entityManager->remove($mouvementProduct);
        $entityManager->flush();
        
        $this->addFlash("success", "la réception a été supprimée avec succès ");
        // Obtenez l'URL de la page précédente
        $referer = $request->headers->get('referer');
        // Si l'URL de référence existe, redirigez l'utilisateur vers cette URL
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirectToRoute('app_logescom_stock_transfert_products_show', ['id' => $transfert_product->getId(), 'lieu_vente' => $lieu_vente->getId(), 'position' => 'reception'], Response::HTTP_SEE_OTHER);
    }
}
