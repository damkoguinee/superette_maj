<?php

namespace App\Controller\Logescom\Direction;

use App\Repository\AchatFournisseurRepository;
use App\Repository\StockRepository;
use App\Repository\CaisseRepository;
use App\Repository\DepensesRepository;
use App\Repository\LivraisonRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use App\Repository\TauxDeviseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use App\Repository\AnomalieProduitRepository;
use App\Repository\CommandeProductRepository;
use App\Repository\ListeProductAchatFournisseurRepository;
use App\Repository\ModificationFactureRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MouvementCollaborateurRepository;
use App\Repository\MouvementProductRepository;
use App\Repository\ProductsRepository;
use App\Repository\SuppressionFactureRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/direction/direction')]
class DirectionController extends AbstractController
{
    #[Route('/logescom/direction/direction', name: 'app_logescom_direction_direction')]
    public function index(): Response
    {
        return $this->render('logescom/direction/direction/index.html.twig', [
            'controller_name' => 'DirectionController',
        ]);
    }

    #[Route('/facture/modification', name: 'app_logescom_direction_facture_modification')]
    public function factureModification(ModificationFactureRepository $modificationFactureRep, LieuxVentesRepository $lieuVenteRep, EntrepriseRepository $entrepriseRep, Request $request): Response
    {

        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("lieu") and $request->get("lieu") != 'général'){
            $search = $request->get("lieu");
            $lieu = $lieuVenteRep->find( $search);
            $modifications = $modificationFactureRep->listeModificationFactureParLieuPaginated($lieu, $date1, $date2, $pageEncours, 25);

        }else{
            $modifications = $modificationFactureRep->listeModificationFacturePaginated($date1, $date2, $pageEncours, 25);
            $search = "";
            $lieu = "";

        }
        return $this->render('logescom/direction/direction/modification_facture.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),
            'modifications' => $modifications,
            'search' => $search,
            'lieu' => $lieu ? $lieu : '',
            'lieux_ventes' => $lieuVenteRep->findAll()
        ]);
    }

    #[Route('/facture/suppression', name: 'app_logescom_direction_facture_suppression')]
    public function factureSuppression(SuppressionFactureRepository $suppressionFactureRep, LieuxVentesRepository $lieuVenteRep, EntrepriseRepository $entrepriseRep, Request $request): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("lieu") and $request->get("lieu") != 'général'){
            $search = $request->get("lieu");
            $lieu = $lieuVenteRep->find( $search);
            $suppressions = $suppressionFactureRep->listeSuppressionFactureParLieuPaginated($lieu, $date1, $date2, $pageEncours, 25);

        }else{
            $suppressions = $suppressionFactureRep->listeSuppressionFacturePaginated($date1, $date2, $pageEncours, 25);
            $search = "";
            $lieu = "";

        }
        return $this->render('logescom/direction/direction/suppression_facture.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),
            'suppressions' => $suppressions,
            'search' => $search,
            'lieu' => $lieu ? $lieu : '',
            'lieux_ventes' => $lieuVenteRep->findAll()
        ]);
    }

    #[Route('/solde', name: 'app_logescom_direction_direction_solde')]
    public function solde(MouvementCollaborateurRepository $mouvementCollaborateurRep, MouvementCaisseRepository $mouvementCaisseRep, StockRepository $stockRep, DepensesRepository $depenseRep, CaisseRepository $caisseRep, AnomalieProduitRepository $anomalieRep, TauxDeviseRepository $tauxDeviseRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep, Request $request, EntityManagerInterface $em): Response
    {
        // changement de taux

        $id_taux = $request->get('id_taux');
        if ($id_taux) {
            $montant = $request->get('montant');
            $montant = preg_replace('/[^0-9,.]/', '', $montant);
            $montant = floatval($montant);
            $taux = $tauxDeviseRep->find($id_taux);
            $taux->setTaux($montant);
            $em->persist($taux);
            $em->flush();
        }
        $annee_en_cours = date('Y');
        if($request->get("lieu")){
            $lieu = $request->get('lieu');
            $soldeGeneral = $mouvementCollaborateurRep->listeSoldeGeneralGroupeParDeviseParCollaborateurParAnneeParLieu($annee_en_cours, $lieu);
            $dettes = [];
            $creances = [];
            foreach ($soldeGeneral as $solde) {
                $montant = floatval($solde['montant']);
                $devise = $solde['devise'];

                if ($montant > 0) {
                    if (!isset($dettes[$devise])) {
                        $dettes[$devise] = 0;
                    }
                    $dettes[$devise] += $montant;
                } else {
                    if (!isset($creances[$devise])) {
                        $creances[$devise] = 0;
                    }
                    $creances[$devise] += $montant;
                }
            }
            $total_dettes = 0;
            $total_creances = 0;
            foreach ($soldeGeneral as $solde) {
                $montant = floatval($solde['montant']);
                $id_devise = $solde['id_devise'];
                $taux_devise = $tauxDeviseRep->findOneBy(["devise" => $id_devise, 'lieuVente' => $this->getUser()->getLieuVente()]);

                $taux = $taux_devise ? $taux_devise->getTaux() : 1;

                if ($montant > 0) {
                    $total_dettes += $montant * $taux;
                } else {
                    $total_creances += $montant * $taux;
                }
            }

            $caisses = $caisseRep->findCaisseByLieuByType($lieu, 'caisse');
            $banques = $caisseRep->findCaisseByLieuByType($lieu, 'banque');
            $solde_caisses = $mouvementCaisseRep->soldeCaisseGeneralParTypeGroupeParDeviseParLieu($caisses, $lieu);
            $total_caisses = 0;
            foreach ($solde_caisses as $caisse) {
                $montant = floatval($caisse['montant']);
                $id_devise = $caisse['id_devise'];
                $taux_devise = $tauxDeviseRep->findOneBy(["devise" => $id_devise, 'lieuVente' => $this->getUser()->getLieuVente()]);

                $taux = $taux_devise ? $taux_devise->getTaux() : 1;
                $total_caisses += $montant * $taux;
                
            }

            $solde_banques = $mouvementCaisseRep->soldeCaisseGeneralParTypeGroupeParDeviseParlieu($banques, $lieu);
            $total_banques = 0;
            foreach ($solde_banques as $caisse) {
                $montant = floatval($caisse['montant']);
                $id_devise = $caisse['id_devise'];
                $taux_devise = $tauxDeviseRep->findOneBy(["devise" => $id_devise, 'lieuVente' => $this->getUser()->getLieuVente()]);

                $taux = $taux_devise ? $taux_devise->getTaux() : 1;
                $total_banques += $montant * $taux;
                
            }
            $stocks = $stockRep->soldeStockGeneralGroupeParStockParLieu($lieu);
            $total_stocks = $stockRep->soldeStockGeneralParlieu($lieu);
            $anomalies = $anomalieRep->totalAnomalieGeneralParLieu($lieu);
            $gain_devise = 0;
            
            $depenses = $depenseRep->totalDepensesParPeriodeParLieu($lieu, date('01-01-Y'), date('31-12-Y'));
            $total_depenses = 0;
            foreach ($depenses as $depense) {
                $montant = floatval($depense['montantTotal']);
                $id_devise = $depense['id_devise'];
                $taux_devise = $tauxDeviseRep->findOneBy(["devise" => $id_devise, 'lieuVente' => $this->getUser()->getLieuVente()]);

                $taux = $taux_devise ? $taux_devise->getTaux() : 1;
                $total_depenses+= $montant * $taux;
                
            }

        }else{
            $soldeGeneral = $mouvementCollaborateurRep->listeSoldeGeneralGroupeParDeviseParCollaborateurParAnnee($annee_en_cours);
            $dettes = [];
            $creances = [];
            foreach ($soldeGeneral as $solde) {
                $montant = floatval($solde['montant']);
                $devise = $solde['devise'];

                if ($montant > 0) {
                    if (!isset($dettes[$devise])) {
                        $dettes[$devise] = 0;
                    }
                    $dettes[$devise] += $montant;
                } else {
                    if (!isset($creances[$devise])) {
                        $creances[$devise] = 0;
                    }
                    $creances[$devise] += $montant;
                }
            }
            $total_dettes = 0;
            $total_creances = 0;
            foreach ($soldeGeneral as $solde) {
                $montant = floatval($solde['montant']);
                $id_devise = $solde['id_devise'];
                $taux_devise = $tauxDeviseRep->findOneBy(["devise" => $id_devise, 'lieuVente' => $this->getUser()->getLieuVente()]);

                $taux = $taux_devise ? $taux_devise->getTaux() : 1;

                if ($montant > 0) {
                    $total_dettes += $montant * $taux;
                } else {
                    $total_creances += $montant * $taux;
                }
            }

            $caisses = $caisseRep->findBy(['type' => 'caisse']);
            $banques = $caisseRep->findBy(['type' => 'banque']);
            $solde_caisses = $mouvementCaisseRep->soldeCaisseGeneralParTypeGroupeParDevise($caisses);
            $total_caisses = 0;
            foreach ($solde_caisses as $caisse) {
                $montant = floatval($caisse['montant']);
                $id_devise = $caisse['id_devise'];
                $taux_devise = $tauxDeviseRep->findOneBy(["devise" => $id_devise, 'lieuVente' => $this->getUser()->getLieuVente()]);

                $taux = $taux_devise ? $taux_devise->getTaux() : 1;
                $total_caisses += $montant * $taux;
                
            }

            $solde_banques = $mouvementCaisseRep->soldeCaisseGeneralParTypeGroupeParDevise($banques);
            $total_banques = 0;
            foreach ($solde_banques as $caisse) {
                $montant = floatval($caisse['montant']);
                $id_devise = $caisse['id_devise'];
                $taux_devise = $tauxDeviseRep->findOneBy(["devise" => $id_devise, 'lieuVente' => $this->getUser()->getLieuVente()]);

                $taux = $taux_devise ? $taux_devise->getTaux() : 1;
                $total_banques += $montant * $taux;
                
            }
            $stocks = $stockRep->soldeStockGeneralGroupeParStock();
            $total_stocks = $stockRep->soldeStockGeneral();
            $anomalies = $anomalieRep->totalAnomalieGeneral();
            $gain_devise = 0;
            
            $depenses = $depenseRep->totalDepensesParPeriode(date('01-01-Y'), date('31-12-Y'));
            $total_depenses = 0;
            foreach ($depenses as $depense) {
                $montant = floatval($depense['montantTotal']);
                $id_devise = $depense['id_devise'];
                $taux_devise = $tauxDeviseRep->findOneBy(["devise" => $id_devise, 'lieuVente' => $this->getUser()->getLieuVente()]);

                $taux = $taux_devise ? $taux_devise->getTaux() : 1;
                $total_depenses+= $montant * $taux;
                
            }
        }
        return $this->render('logescom/direction/direction/solde.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),
            'dettes' => $dettes,
            'total_dettes' => $total_dettes,
            'creances' => $creances,
            'total_creances' => $total_creances,
            'total_stocks' => $total_stocks,
            'caisses' => $solde_caisses,
            'banques' => $solde_banques,
            'total_caisses' => $total_caisses,
            'total_banques' => $total_banques,
            'stocks' => $stocks,
            'anomalies' => $anomalies,
            'gain_devise' => $gain_devise,
            'total_depenses' => $total_depenses,
            'taux_devise' => $tauxDeviseRep->findBy(['lieuVente' => $this->getUser()->getLieuVente()]),
            'lieux_ventes' => $lieuVenteRep->findAll(),
        ]);
    }

    #[Route('/stock', name: 'app_logescom_direction_direction_stock')]
    public function stock(Request $request, ListeStockRepository $listeStockRep, StockRepository $stockRep,  EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep, EntityManagerInterface $em): Response
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
            $magasin = $listeStockRep->findOneBy([]);

        }

        // partie ajusteent prix de vente des produits

        if ($request->get("ajustement") and $request->get("id_stock")){
            $prixAchat = floatval(preg_replace('/[^-0-9,.]/', '', $request->get("prix_achat")));
            $prixRevient = floatval(preg_replace('/[^-0-9,.]/', '', $request->get("prix_revient")));
            $prixVente = floatval(preg_replace('/[^-0-9,.]/', '', $request->get("prix_vente")));
            if (is_numeric($prixVente)) {
                $stock_product = $stockRep->find($request->get("id_stock"));
                if ($request->get("peremption")) {
                    $stock_product->setDatePeremption(new \DateTime($request->get("peremption")));
                }
                $stock_product->setPrixVente($prixVente)
                            ->setPrixAchat($prixAchat)
                            ->setPrixRevient($prixRevient);
                $em->persist($stock_product);
                $em->flush();

                return new RedirectResponse($this->generateUrl('app_logescom_direction_direction_stock', ['search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)])); 
            }
            

        }
        if ($request->get('type') and $request->get('type') != 'général') {
            $stocks = $stockRep->stockParTypePaginated($magasin, $request->get('type'), $pageEncours, 2000); 
            $type = $request->get('type');
        }else{
            $stocks = $stockRep->findStocksPaginated($magasin, $search, $pageEncours, 2000); 
            $type = 'général';
        }
        $listeStocks = $listeStockRep->findAll();
        return $this->render('logescom/direction/direction/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'liste_stocks' => $listeStocks,
            'magasin' => $magasin,
            'search' => $search,
            'stocks' => $stocks,
            'lieu_vente' => $lieuVenteRep->find(1),
            'type' => $type,
            'lieu_vente' => $this->getUser()->getLieuVente(),
        ]);
    }

    #[Route('/produit/vente', name: 'app_logescom_direction_direction_produit_vente', methods: ['GET'])]
    public function venteProduit(Request $request, LieuxVentesRepository $lieuVenteRep, EntrepriseRepository $entrepriseRep, LivraisonRepository $livraisonRep, CommandeProductRepository $commandeProdRep, EntityManagerInterface $em): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");
        }else{
            $date1 = date("Y-m-d");
            $date2 = date("Y-m-d");
        }

        $pageEncours = $request->get('pageEncours', 1);
        $pageEncoursLiv = $request->get('pageEncoursLiv', 1);

        if ($request->get("lieu") and $request->get('lieu') != 'general') {
            $lieu_vente = $lieuVenteRep->find($request->get('lieu'));

            $commandes_groupes = $commandeProdRep->listeDesProduitsVendusGroupeParPeriodeParLieu($date1, $date2, $lieu_vente);
    
            $livraisons_groupes = $livraisonRep->listeDesProduitsLivresGroupeParPeriodeParLieu($date1, $date2, $lieu_vente);

            $commandes = $commandeProdRep->listeDesProduitsVendusParPeriodeParLieuPagine($date1, $date2, $lieu_vente, $pageEncours, 1000);

            // $livraisons = $livraisonRep->listeDesProduitsLivresParPeriodeParLieuPagine($date1, $date2, $lieu_vente, $pageEncoursLiv, 30);

        }else{
            $commandes_groupes = $commandeProdRep->listeDesProduitsVendusGroupeParPeriode($date1, $date2);
    
            $livraisons_groupes = $livraisonRep->listeDesProduitsLivresGroupeParPeriode($date1, $date2);

            $commandes = $commandeProdRep->listeDesProduitsVendusParPeriodePagine($date1, $date2, $pageEncours, 1000);
            
            // $livraisons = $livraisonRep->listeDesProduitsLivresParPeriodePagine($date1, $date2, $pageEncoursLiv, 30);
        }

        

        return $this->render('logescom/direction/direction/produits_vendus.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieuVenteRep->find(1),
            'lieux' => $lieuVenteRep->findAll(),
            'commandes_groupes' => $commandes_groupes,
            'commandes' => $commandes,
            // 'livraisons' => $livraisons,
            'livraisons_groupes' => $livraisons_groupes,
            'date1' => $date1,
            'date2' => $date2,
            'search' => ""
        ]);
    }


    #[Route('/bilan/produit', name: 'app_logescom_direction_direction_bilan_produit', methods: ['GET'])]
    public function bilanProduit(Request $request, LieuxVentesRepository $lieuVenteRep, EntrepriseRepository $entrepriseRep, ProductsRepository $productsRep, StockRepository $stockRep, AnomalieProduitRepository $anomalieProdRep, MouvementProductRepository $mouvementProductRep, AchatFournisseurRepository $achatFournisseurRep, ListeProductAchatFournisseurRepository $listeAchatRep, LivraisonRepository $livraisonRep, CommandeProductRepository $commandeProdRep, EntityManagerInterface $em): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");
        }else{
            $date1 = date("Y-m-d");
            $date2 = date("Y-m-d");
        }

        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }

        $pageEncours = $request->get('pageEncours', 1);

        $products = $productsRep->findProductsAdminPaginated($search, $pageEncours, 20);

        if ($request->get("lieu") and $request->get('lieu') != 'general') {
            $lieu_vente = $lieuVenteRep->find($request->get('lieu'));

            $bilanProducts = [];
            foreach ($products['data'] as $product) {
                $verif = $mouvementProductRep->findOneBy(['product' => $product]);
                if ($verif) {
                    $achats = $listeAchatRep->totalAchatGeneralParProduitParPeriodeParLieu($product, $date1, $date2, $lieu_vente);
                    $ventes = $commandeProdRep->topVenteGeneralParProduitParPeriodeParLieu($product, $date1, $date2, $lieu_vente);
                    $anomalies = $anomalieProdRep->totalAnomalieParProduitParPeriodeParLieu($product, $date1, $date2, $lieu_vente);
                    $qtiteVar = $mouvementProductRep->totalStockParProduitParPeriodeParLieu($product, $date1, $date2, $lieu_vente);
                    $stocks = $stockRep->totalQuantiteParProduitParLieu($product, $lieu_vente);
                    $resteAlivrer = $commandeProdRep->resteAlivrerParProduitParLieu($product, $lieu_vente);
                                       
                    $bilanProducts[] = [
                        'product' => $product,
                        'achats' => $achats,
                        'ventes' => $ventes,
                        'anomalies' => $anomalies,
                        'stocks' => $stocks,
                        'qtite_var' => $qtiteVar,
                        'resteLivraison' => $resteAlivrer,
                    ];
                }
                
            }

        }else{
            $lieu_vente = "general";
            $bilanProducts = [];
            foreach ($products['data'] as $product) {
                $verif = $mouvementProductRep->findOneBy(['product' => $product]);
                if ($verif) {
                    $achats = $listeAchatRep->totalAchatGeneralParProduitParPeriode($product, $date1, $date2);
                    $ventes = $commandeProdRep->topVenteGeneralParProduitParPeriode($product, $date1, $date2);
                    $anomalies = $anomalieProdRep->totalAnomalieParProduitParPeriode($product, $date1, $date2);
                    $qtiteVar = $mouvementProductRep->totalStockParProduitParPeriode($product, $date1, $date2);
                    $stocks = $stockRep->totalQuantiteParProduit($product);
                    $resteAlivrer = $commandeProdRep->resteAlivrerParProduit($product);
                   
                    $bilanProducts[] = [
                        'product' => $product,
                        'achats' => $achats,
                        'ventes' => $ventes,
                        'anomalies' => $anomalies,
                        'stocks' => $stocks,
                        'qtite_var' => $qtiteVar,
                        'resteLivraison' => $resteAlivrer,
                    ];
                }
                
            }
        }
        return $this->render('logescom/direction/direction/bilan_produit.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieuVenteRep->find(1),
            'lieux' => $lieuVenteRep->findAll(),
            'bilan_products' => $bilanProducts,
            'date1' => $date1,
            'date2' => $date2,
            'search' => "",
            'search_lieu' => $lieu_vente
        ]);
    }
}
