<?php

namespace App\Controller\Logescom\Vente;

use FFI;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Livraison;
use App\Entity\Facturation;
use App\Entity\LieuxVentes;
use App\Entity\CommandeProduct;
use App\Entity\CommissionVente;
use App\Entity\FactureFraisSup;
use App\Entity\MouvementCaisse;
use App\Entity\MouvementProduct;
use App\Repository\UserRepository;
use App\Entity\ModificationFacture;
use App\Repository\StockRepository;
use App\Repository\CaisseRepository;
use App\Repository\ClientRepository;
use App\Repository\DeviseRepository;
use App\Entity\MouvementCollaborateur;
use App\Entity\SuppressionFacture;
use App\Repository\FraisSupRepository;
use App\Repository\ProductsRepository;
use App\Repository\LivraisonRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use App\Repository\TauxDeviseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FacturationRepository;
use phpDocumentor\Reflection\Types\Null_;
use App\Repository\ModePaiementRepository;
use App\Repository\LiaisonProduitRepository;
use App\Repository\CommandeProductRepository;
use App\Repository\CommissionVenteRepository;
use App\Repository\CompteOperationRepository;
use App\Repository\FactureFraisSupRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MouvementProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Repository\ConfigurationLogicielRepository;
use App\Repository\ModificationFactureRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\MouvementCollaborateurRepository;
use App\Repository\PersonnelRepository;
use App\Repository\ProformatRepository;
use App\Repository\PromotionRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/logescom/vente/facturation')]
class FacturationController extends AbstractController
{
    #[Route('/vente/{lieu_vente}', name: 'app_logescom_vente_facturation_vente')]
    public function index(Request $request, ProductsRepository $productsRep, StockRepository $stockRep, UserRepository $userRep, MouvementCollaborateurRepository $mouvementCollabRep, DeviseRepository $deviseRep, TauxDeviseRepository $tauxDeviseRep, CaisseRepository $caisseRep, ModePaiementRepository $modePaieRep, ListeStockRepository $listeStockRep, SessionInterface $session, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep, FraisSupRepository $fraisSupRep, ProformatRepository $proformatRep, EntityManagerInterface $em): Response
    {
        $session_client = $session->get("session_client", null);
        $session_client_com = $session->get("session_client_com", null);
        $session_nom_client_cash = $session->get("session_nom_client_cash", null);
        $session_remise_glob = $session->get("session_remise_glob", 0);
        $versement = $session->get("versement", []);
        $cheque = $session->get("cheque", []);
        $frais_sup = $session->get("frais_sup", []);
        $panier = $session->get('panier', []);
        $paiement = $session->get('paiement', []);

        if (empty($session_proformat)) {
            $proformat = $request->get("confirmerProformat") ? $proformatRep->find($request->get("confirmerProformat")) : null;
        }
        $session_proformat = $session->get("session_proformat", $proformat);
        $session->set("session_proformat", $session_proformat);
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

            $search = $request->query->get('search_client');
            if ($search) {
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

            $search = $request->query->get('search_all_user');
            if ($search) {
                $clients = $userRep->findUserSearchByLieu($search, $lieu_vente);    
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

        if ($request->get("id_product_search")){
            $product = $request->get("id_product_search");
            $product = $productsRep->find($product);
            $stock_vente = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente, 'type' => 'vente']);
            $stock_product = $stockRep->findOneBy(['product' => $product, 'stockProduit' => $stock_vente]);

            $stocks_lieu = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);
            $stock_dispo = $stockRep->sumQuantiteProduct($product, $stocks_lieu);
        }elseif($request->get("scanner")){
            $code_barre = $request->get("scanner");
            $product = $productsRep->findOneBy(['codeBarre' => $code_barre]);
            $stock_vente = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente, 'type' => 'vente']);
            $stock_product = $stockRep->findOneBy(['product' => $product, 'stockProduit' => $stock_vente]);

            $stocks_lieu = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);
            $stock_dispo = $stockRep->sumQuantiteProduct($product, $stocks_lieu);

            if (!$product) {
                $product = [];
                $stock_product = [];
            }

        }else{
            $product = array();
            $stock_product = array();
        }

        if ($request->get("id_client_search")){
            $client = $request->get("id_client_search");
            $client = $userRep->find($client);
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client);
            $session_client= $client;            
        }else{
            $client = array();
            $soldes_collaborateur = array();
            if (empty($session_client)) {
                $session_client= array();
            }
        }

        if ($request->get("id_user")){
            $client_com = $request->get("id_user");
            $client_com = $userRep->find($client_com);
            $soldes_collaborateur_com = $mouvementCollabRep->findSoldeCollaborateur($client_com);
            $session_client_com= $client_com;            
        }else{
            $client_com = array();
            $soldes_collaborateur_com = array();
            if (empty($session_client_com)) {
                $session_client_com= array();
            }
        }

        if ($request->get("nom_client_cash")){
            $session_nom_client_cash = $request->get("nom_client_cash");
        }else{
            if (empty($session_nom_client_cash)) {
                $session_nom_client_cash = array();
            }
        }

        if ($request->get("remise_glob")!== null){
            $montant_remise = floatval(preg_replace('/[^-0-9,.]/', '', $request->get("remise_glob")));
            $session_remise_glob = $montant_remise;
        }else{
            if (empty($session_remise_glob)) {
                $session_remise_glob = 0;
            }
        }

        if ($request->get("versement_banque") !== null){
            $montantString = preg_replace('/[^-0-9,.]/', '', $request->get('versement_banque'));
            if (empty($versement)) {
                $versement =[
                    'montant' => floatval($montantString), 
                    'banque' => $caisseRep->find($request->get("caisse")) 
                ] ;
            }else{
                $versement =[
                    'montant' => floatval($montantString), 
                    'banque' => $caisseRep->find($request->get("caisse")) 
                ] ;
            }

            $session->set('versement', $versement);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_vente', ['lieu_vente' => $lieu_vente->getId(), 'update' => 'true']));  
        }

        if ($request->get("banque_cheque") !== null ){
            $montantString = preg_replace('/[^-0-9,.]/', '', $request->get('montant_cheque'));
            if (empty($cheque)) {
                $cheque =[
                    'montant' => floatval($montantString), 
                    'numero_cheque' => $request->get("numero_cheque"),
                    'banque' => $request->get("banque_cheque") 
                ] ;
            }else{
                $cheque =[
                    'montant' => floatval($montantString),
                    'numero_cheque' => $request->get("numero_cheque"),
                    'banque' => $request->get("banque_cheque") 
                ] ;
            }
            $session->set('cheque', $cheque);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_vente', ['lieu_vente' => $lieu_vente->getId(), 'update' => 'true']));
        }

        if ($request->get("montant_frais_sup") !== null){
            $montantString = floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montant_frais_sup')));

            if ($montantString !== null) {
                // Vérifier si le type existe déjà dans les paiements
                $fraisExistante = false;
                foreach ($frais_sup as $indice => $item) {
                    // Si le type existe déjà, mettre à jour le montant correspondant
                    if ($item['type'] == $request->get("type")) {
                        $frais_sup[$indice]['montant'] = $montantString;
                        $fraisExistante = true;
                        break;
                    }
                }
                // Si le type n'existe pas encore, l'ajouter
                if (!$fraisExistante) {
                    $frais_sup[] =[
                        'montant' => floatval($montantString), 
                        'type' => $request->get("type") 
                    ] ;
                }
            }
            $session->set('frais_sup', $frais_sup);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_vente', ['lieu_vente' => $lieu_vente->getId()]));
        }

        $qtite = $request->query->get("qtite", 1);
        $prix_vente = $request->get('prix_vente', $stock_product ? $stock_product->getPrixVente() : 0);
        $remise = $request->get('remise', 0);
        $dejaAjouter = false;
        if ($request->get("id_product_search") OR $request->get("scanner")){
            foreach ($panier as $indice => $item) {
                if ($item['stock']->getProducts()->getId() == $stock_product->getProducts()->getId()) {
                    if ($request->get('update')) {

                        $prixString = preg_replace('/[^-0-9,.]/', '', $request->get('prix_vente'));
                        $qtiteString = preg_replace('/[^-0-9,.]/', '', $request->get('qtite'));
                        $remiseString = preg_replace('/[^-0-9,.]/', '', $request->get('remise'));
                        $prixVenteUpdate = floatval($prixString);
                        $qtiteUpdate = floatval($qtiteString);
                        $remiseUpdate = floatval($remiseString);

                        $panier[$indice]['qtite'] = $qtiteUpdate;
                        $panier[$indice]['prixVente'] = $prixVenteUpdate ;
                        $panier[$indice]['remise'] = $remiseUpdate ;

                    }else{
                        $panier[$indice]['qtite'] += $qtite;
                    }
                    $item['client'] = $client;

                    $dejaAjouter= true;
                    break;
                }
            }
            if ($stock_product) {
                if (!$dejaAjouter) {
                    $panier[]=["stock"=>$stock_product, "prixVente" => $prix_vente, "qtite" => $qtite, 'remise' => $remise, 'dispo' => $stock_dispo ];
                }
            }
            $session->set("panier", $panier);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_vente', ['lieu_vente' => $lieu_vente->getId()]));
        }

        if ($request->get("id_devise")){
            $devises = $deviseRep->findAll();

            if (empty($paiement)) {
                foreach ($devises as $devise) {
                    $taux = $tauxDeviseRep->findOneBy(['devise' => $devise, 'lieuVente' => $lieu_vente]);
                    $taux = $taux ? $taux->getTaux() : 1;
                    $paiement[$devise->getNomDevise()] = ['montant' => 0, 'taux' => $taux];
                }
            }

            foreach ($devises as $key => $devise) {
                
                $montant = $request->get("montant_".$devise->getId());
                if (isset($montant)) {
                    $montant = floatval(preg_replace('/[^-0-9,.]/', '', $montant));
                    $taux = $tauxDeviseRep->findOneBy(['devise' => $devise, 'lieuVente' => $lieu_vente]);
                    $taux = $taux ? $taux->getTaux() : 1;
                    $paiement[$devise->getNomDevise()] = ['montant' => $montant, 'taux' => $taux];
                }

                
            }
            $session->set("paiement", $paiement);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_vente', ['lieu_vente' => $lieu_vente->getId(), 'update' => 'true']));
        }else{
            if (!$request->get('update')) {
                
                $total = 0;
                foreach ($panier as $article) {
                    $prixVente = $article['prixVente']; 
                    $quantite = $article['qtite'];
                    $sousTotal = ($prixVente  - $article['remise'] ) * $quantite - $session_remise_glob;
                    $total += $sousTotal;
                }
                $paiement['gnf'] = ['montant' => $total, 'taux' => 1];
                $session->set("paiement", $paiement);
            }

        }
        // $session = $request->getSession();
        // $session->remove("paiement");
        if ($request->get('id_taux')) {
            $taux_devise = $tauxDeviseRep->findBy(['lieuVente' => $lieu_vente]);
            foreach ($taux_devise as $key => $taux) {
                $taux = $request->get($taux->getId());
                $taux = floatval(preg_replace('/[^-0-9,.]/', '', $taux));

                if ($taux) {
                    $taux_devise = $tauxDeviseRep->find($request->get('id_taux'));
                    $taux_devise->setTaux($taux);
                    $em->persist($taux_devise);
                    $em->flush();
                }
            }
        }

        
        $session->set("session_client", $session_client);
        $session->set("session_nom_client_cash", $session_nom_client_cash);
        $session->set("session_client_com", $session_client_com);
        $session->set("session_remise_glob", $session_remise_glob);
        $session->set('versement', $versement);
        
        
        return $this->render('logescom/vente/facturation/vente.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search_product' => $product,
            'search_client' => $client,
            'client_com' => $client_com,
            'soldes_collaborateur' => $soldes_collaborateur,
            'devises' => $deviseRep->findAll(),
            'taux_devise'       => $tauxDeviseRep->findBy(['lieuVente' => $lieu_vente]),
            'modes_paie' => $modePaieRep->findAll(),
            'frais_sups' => $fraisSupRep->findAll(),
            'caisses_banque' => $caisseRep->findCaisseByLieuByType($lieu_vente, 'banque'), 
            'caisses' => $caisseRep->findCaisseByLieu($lieu_vente), 
            'liste_stocks' => $listeStockRep->findBy(['lieuVente' => $lieu_vente]),
            'panier' => $panier,
            'session_client' => $session_client,
            'session_client_com' => $session_client_com,
            'session_nom_client_cash' => $session_nom_client_cash,
            'session_remise_glob' => $session_remise_glob,
            'paiements' => $paiement,
            'versement' => $versement,
            'cheque' => $cheque,
            'frais' => $frais_sup,
            'proformat' => $session_proformat,
        ]);
    }

    #[Route('/valdation/{lieu_vente}', name: 'app_logescom_vente_facturation_validation')]
    public function validation(LieuxVentes $lieu_vente, Request $request, FacturationRepository $facturationRep, StockRepository $stockRep, ListeStockRepository $listeStockRep, CaisseRepository $caisseRep, DeviseRepository $deviseRep, FraisSupRepository $fraisSupRep, CategorieOperationRepository $categorieOpRep, CompteOperationRepository $compteOpRep,  SessionInterface $session, ModePaiementRepository $modePaieRep, UserRepository $userRep, ProductsRepository $productRep, LiaisonProduitRepository $liaisonRep, ProformatRepository $proformatRep, PromotionRepository $promotionRep, EntityManagerInterface $em): Response
    {
        $session_client = $session->get("session_client", '');
        $session_client_com = $session->get("session_client_com", '');
        $session_nom_client_cash = $session->get("session_nom_client_cash", '');
        $session_remise_glob = $session->get("session_remise_glob", '');
        $versement = $session->get("versement", '');
        $cheque = $session->get("cheque", '');
        $frais_sups = $session->get("frais_sup", '');
        $panier = $session->get("panier", []);
        $paiements = $session->get("paiement", []);

        if ($panier) {
            $devise = $deviseRep->find(1);
            $init = $lieu_vente->getInitial();
            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('y');
            $idSuivant =($facturationRep->findMaxId() + 1);
            $numeroFacture = $init.$referenceDate . sprintf('%04d', $idSuivant);

            $totalFacture =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('totalFacture')));
            $totalFacture = $totalFacture ? $totalFacture : 0;
            $montantPaye =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montantPaye')));
            $montantPaye = $montantPaye ? $montantPaye : 0;
            $reste =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('reste')));
            $reste = $reste ? $reste : 0;
            $rendu =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('rendu')));
            $rendu = $rendu ? $rendu : 0;
            $montantRemise =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montantRemise')));
            $frais =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('frais')));
            $frais = $frais ? $frais : 0 ;
            $montant_com =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montant_com')));
            $montant_com = $montant_com ? $montant_com : 0 ;
            $livraison = $request->get('livraison');
            $stockLivraison = $livraison ? $listeStockRep->find($livraison) : null;
            $caisse = $request->get('caisse') ? $caisseRep->find($request->get('caisse')) : null;
            $dateVente = $request->get('date_vente') ? (new \DateTime($request->get('date_vente'))) : $dateDuJour ;
            $dateAlerte = $request->get('date_alerte') ? (new \DateTime($request->get('date_alerte'))) : null ;

            $commentaire = $request->get('commentaire') ? ($request->get('commentaire')) : null ;
            $proformat= $request->get('proformat') ? ($proformatRep->find($request->get('proformat'))) : null ;

            $client = $session_client ? $userRep->find($session_client->getId()) : null;

            // preparation de la facture
            $factures = new Facturation();
            if ($montantPaye > $totalFacture) {
                // gestion dans le cas il y a un rendu
                $montantPaye = $totalFacture;
            }
            $factures->setNumeroFacture($numeroFacture)
                    ->setProformat($proformat)
                    ->setTotalFacture($totalFacture)
                    ->setMontantPaye($montantPaye)
                    ->setMontantRemise($montantRemise)
                    ->setFraisSup($frais)
                    ->setClient($client)
                    ->setEtat(($totalFacture == $montantPaye) ? "payé" : ($montantPaye ? 'paie partiel' : 'non payé'))
                    ->setEtatLivraison($livraison ? "livré" : "non livré")
                    ->setCaisse($caisse)
                    ->setModePaie($modePaieRep->find(1))
                    ->setLieuVente($lieu_vente)
                    ->setSaisiePar($this->getUser())
                    ->setDateFacturation($dateVente)
                    ->setDateSaisie(new \DateTime('now'))
                    ->setDateAlerte($dateAlerte)
                    ->setNomClientCash($session_nom_client_cash ? $session_nom_client_cash : null)
                    ->setCommentaire($commentaire);
            $em->persist($factures);
            // enregistrement des frais sup
            if ($frais) {
                foreach ($frais_sups as $frais_sup) {
                    if ($frais_sup['type']) {
                        $type = $fraisSupRep->find($frais_sup['type']);
                        $factureFrais = new FactureFraisSup();
                        $factureFrais->setMontant($frais_sup['montant'])
                                ->setDevise($devise)
                                ->setFraisSup($type);
                        $factures->addFactureFraisSup($factureFrais);
                        $em->persist($factureFrais);

                        
                    }
                }

                $mouvementCaisseFrais = new MouvementCaisse();
                $mouvementCaisseFrais->setLieuVente($lieu_vente)
                        ->setCaisse($caisse)
                        ->setModePaie($modePaieRep->find(1))
                        ->setCategorieOperation($categorieOpRep->find(1))
                        ->setCompteOperation($compteOpRep->find(6))
                        ->setTypeMouvement('frais sup')
                        ->setMontant(-$frais)
                        ->setDevise($devise)
                        ->setSaisiePar($this->getUser())
                        ->setTaux(1)
                        ->setDateOperation($dateVente)
                        ->setDateSaisie(new \DateTime("now"));
                $factures->addMouvementCaiss($mouvementCaisseFrais);
                $em->persist($mouvementCaisseFrais);

            }

            if ($request->get("id_client_com")) {
                $commission = new CommissionVente();
                $client_com = $userRep->find($request->get("id_client_com"));
                $commission->setBeneficiaire($client_com)
                        ->setMontant($montant_com)
                        ->setDatePaiement($dateVente);
                $factures->addCommissionVente($commission);
                $em->persist($commission);

                if ($reste == 0) {
                    // si la facture est payée on credite le compte du bénéficiaire
                    $mouvementCollabCom = new MouvementCollaborateur ();
                    $mouvementCollabCom->setCollaborateur($client_com)
                            ->setOrigine('commission')
                            ->setMontant($montant_com)
                            ->setDevise($devise)
                            ->setCaisse($caisse)
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser())
                            ->setDateOperation($dateVente)
                            ->setDateSaisie(new \DateTime('now'));
                    $factures->addMouvementCollaborateur($mouvementCollabCom);
                    $em->persist($mouvementCollabCom);
                }
            }

            // enregistrement dans le mouvement caisse

            if ($montantPaye) {
                // paiement espèces
                if ($paiements) {
                    foreach ($paiements as $key => $paiement) {
                        $devisePaie = $deviseRep->findOneBy(['nomDevise' => $key ]);
                        $montant = $paiement['montant'];
                        $taux = $paiement['taux'];
                        // gerer gestion de rendu à ce niveau 
                        if ($montant > $montantPaye) {
                            $montant = $totalFacture;
                        }
                        if ($montant) {
                            $mouvementCaisseEspeces = new MouvementCaisse();
                            $mouvementCaisseEspeces->setLieuVente($lieu_vente)
                                    ->setCaisse($caisse)
                                    ->setModePaie($modePaieRep->find(1))
                                    ->setCategorieOperation($categorieOpRep->find(1))
                                    ->setCompteOperation($compteOpRep->find(6))
                                    ->setTypeMouvement('facturation')
                                    ->setMontant($montant)
                                    ->setDevise($devisePaie)
                                    ->setSaisiePar($this->getUser())
                                    ->setTaux($taux)
                                    ->setDateOperation($dateVente)
                                    ->setDateSaisie(new \DateTime("now"));
                            $factures->addMouvementCaiss($mouvementCaisseEspeces);
                            $em->persist($mouvementCaisseEspeces);
                        }

                    }
                }

                // paiement par cheque
                if ($cheque) {
                    $mouvementCaisseCheque = new MouvementCaisse();
                    $mouvementCaisseCheque->setLieuVente($lieu_vente)
                            ->setCaisse($caisse)
                            ->setModePaie($modePaieRep->find(4))
                            ->setNumeroPaie($cheque['numero_cheque'])
                            ->setBanqueCheque($cheque['banque'])
                            ->setCategorieOperation($categorieOpRep->find(1))
                            ->setCompteOperation($compteOpRep->find(6))
                            ->setTypeMouvement('facturation')
                            ->setMontant($cheque['montant'])
                            ->setSaisiePar($this->getUser())
                            ->setDevise($devise)
                            ->setDateOperation($dateVente)
                            ->setDateSaisie(new \DateTime("now"));
                    $factures->addMouvementCaiss($mouvementCaisseCheque);
                    $em->persist($mouvementCaisseCheque);

                }

                // paiement par versement
                if ($versement) {
                    $banque = $caisseRep->find($versement['banque']);
                    $mouvementCaisseVersement = new MouvementCaisse();
                    $mouvementCaisseVersement->setLieuVente($lieu_vente)
                            ->setCaisse($banque)
                            ->setModePaie($modePaieRep->find(3))
                            ->setBanqueCheque($versement['banque']->getDesignation())
                            ->setCategorieOperation($categorieOpRep->find(1))
                            ->setCompteOperation($compteOpRep->find(6))
                            ->setTypeMouvement('facturation')
                            ->setMontant($versement['montant'])
                            ->setDevise($devise)
                            ->setSaisiePar($this->getUser())
                            ->setDateOperation($dateVente)
                            ->setDateSaisie(new \DateTime("now"));
                    $factures->addMouvementCaiss($mouvementCaisseVersement);
                    $em->persist($mouvementCaisseVersement);

                }
            }
            // dd($rendu,$session_client);

            // enregistrement dans le compte du collaborateur s'il n y a pas de paiement
            
            if ($session_client) {
                
                $mouvementCollab = new MouvementCollaborateur ();
                $mouvementCollab->setCollaborateur($client)
                        ->setOrigine('facturation')
                        ->setDevise($devise)
                        ->setCaisse($caisse)
                        ->setLieuVente($lieu_vente)
                        ->setTraitePar($this->getUser())
                        ->setDateOperation($dateVente)
                        ->setDateSaisie(new \DateTime('now'));
                $factures->addMouvementCollaborateur($mouvementCollab);

                if ($rendu >= 0) {                
                    $mouvementCollab->setMontant(-$rendu);
                }else{
                    $mouvementCollab->setMontant(0); 
                }
                $em->persist($mouvementCollab);
            }

            $bonusApplies = []; // Pour stocker les bonus déjà appliqués pour éviter les doublons

            foreach ($panier as $item) {
                $product = $productRep->find($item['stock']->getProducts()->getId());
            
                // Trouver les promotions associées à ce produit
                $promotions = $promotionRep->findPromotionByProduct($product);
            
                foreach ($promotions as $promotion) {
                    $totalQuantity = 0;
                    $promoProductsIds = [];
            
                    // Calculer la somme des quantités des produits du panier présents dans la promotion
                    foreach ($promotion->getProduits() as $promoProduct) {
                        foreach ($panier as $cartItem) {
                            if ($cartItem['stock']->getProducts()->getId() === $promoProduct->getId()) {
                                $totalQuantity += $cartItem['qtite'];
                                $promoProductsIds[] = $promoProduct->getId(); // Collecter les IDs des produits liés à la promo
                            }
                        }
                    }
            
                    // Créer une clé unique pour les produits liés à la promotion
                    sort($promoProductsIds); // Trier les IDs pour assurer la cohérence
                    $bonusKey = $promotion->getId() . '_' . implode('_', $promoProductsIds);
            
                    // Si la somme des quantités est supérieure ou égale à la quantité minimale requise
                    if ($totalQuantity >= $promotion->getQuantiteMin() && !isset($bonusApplies[$bonusKey])) {
                        // Calculer le facteur de la quantité bonus
                        $factor = floor($totalQuantity / $promotion->getQuantiteMin());
            
                        // Initialiser la quantité bonus en fonction du facteur
                        $quantiteOfferte = $factor * $promotion->getQuantiteBonus();
                        if ($factor >= 4 && $factor < 8) {
                            $quantiteOfferte += $promotion->getQuantiteBonus();
                        } elseif ($factor >= 8 && $factor < 9) {
                            $quantiteOfferte += 2 * $promotion->getQuantiteBonus() + 1;
                        }elseif ($factor >= 9 && $factor < 10) {
                            $quantiteOfferte += 2 * $promotion->getQuantiteBonus() + 1;
                        }elseif ($factor >= 10) {
                            $quantiteOfferte += 2 * $promotion->getQuantiteBonus() + 1.5;
                        }
            
                        // Trouver le produit du panier avec la plus grande quantité et le plus grand stock disponible parmi les produits liés
                        $maxQuantityItem = null;
                        foreach ($panier as $cartItem) {
                            if (in_array($cartItem['stock']->getProducts()->getId(), $promoProductsIds)) {
                                if (!$maxQuantityItem || $cartItem['qtite'] > $maxQuantityItem['qtite']) {
                                    $maxQuantityItem = $cartItem;
                                }
                            }
                        }
            
                        // Vérifier si le produit bonus existe déjà dans le panier
                        $bonusExists = false;
                        foreach ($panier as $cartItem) {
                            if ($cartItem['stock']->getProducts()->getId() === $maxQuantityItem['stock']->getProducts()->getId() && $cartItem['prixVente'] == 0) {
                                $bonusExists = true;
                                break;
                            }
                        }
                        // Si le produit bonus n'est pas déjà dans le panier, l'ajouter
                        if (!$bonusExists) {
                            $panier[] = [
                                'stock' => $maxQuantityItem['stock'],  // Stock du produit sélectionné
                                'prixVente' => 0,  // Prix de la promotion (0 pour le bonus)
                                'qtite' => $quantiteOfferte,  // Quantité bonus calculée
                                'remise' => 0,  // Gérer la remise si nécessaire
                                'dispo' => null  // Autre information de disponibilité si nécessaire
                            ];
                        }
            
                        // Marquer la promotion comme appliquée pour cet ensemble de produits
                        $bonusApplies[$bonusKey] = true;
                    }
                }
            }
            // insertion des produits dans la bdd

            foreach ($panier as  $item) {
                $commande = new CommandeProduct();
                $product = $productRep->find($item['stock']->getProducts()->getId());
                $commande->setProduct($product)
                        ->setPrixVente($item['prixVente'])
                        ->setPrixAchat($item['stock']->getPrixAchat())
                        ->setPrixRevient($item['stock']->getPrixRevient())
                        ->setRemise($item['remise'])
                        ->setTva($product->getTva())
                        ->setQuantite($item['qtite']);
                $factures->addCommandeProduct($commande);

                $promotion = $promotionRep->findPromotionByProduct($product);
                // dd($promotion);
                if ($request->get('livraison')) {
                    $stock_selection = $stockRep->findOneBy(['product' => $item['stock']->getProducts(), 'stockProduit' => $stockLivraison]);
                    
                    $commande->setQuantiteLivre($item['qtite']);
                    $livraison = new Livraison();
                    $livraison->setQuantiteLiv($item['qtite'])
                            ->setSaisiePar($this->getUser())
                            ->setStock($stockLivraison)
                            ->setCommentaire("livraison direct")
                            ->setDateLivraison($dateVente)
                            ->setDateSaisie(new \DateTime("now"));
                    $commande->addLivraison($livraison);
                    // on met à jour le stock
                    $stocks = $stockRep->findOneBy(['stockProduit' => $stockLivraison, 'product' => $item['stock']->getProducts()]);
                    $stocks->setQuantite(($stock_selection->getQuantite() - $item['qtite']));
                    
                    // on insere dans le mouvement product
                    $mouvementProduct = new MouvementProduct();
                    $mouvementProduct->setStockProduct($stockLivraison)
                                ->setProduct($product)
                                ->setQuantite(-$item['qtite'])
                                ->setLieuVente($lieu_vente)
                                ->setPersonnel($this->getUser())
                                ->setDateOperation($dateVente)
                                ->setOrigine("vente direct")
                                ->setDescription("vente direct")
                                ->setClient($client);
                    $livraison->addMouvementProduct($mouvementProduct);

                    // on gère la liaison des produits
                    if ($stocks->getQuantite() <= 0) {
                        $liaison = $liaisonRep->findOneBy(['produit2' => $product]);
                        if ($liaison) {
                        
                            $product_liaison_1 = $liaison->getProduit1();

                            $stock_liaison_1 = $stockRep->findOneBy(['stockProduit' => $stockLivraison, 'product' => $product_liaison_1]);
                            if ($liaison->getType() == 'detail') {
                                $stock_liaison_1->setQuantite($stock_liaison_1->getQuantite() - 1);
                                $em->persist($stock_liaison_1);

                                $mouvement_liaison_1 = new MouvementProduct();
                                $mouvement_liaison_1->setStockProduct($stockLivraison)
                                            ->setProduct($product_liaison_1)
                                            ->setQuantite(-1)
                                            ->setLieuVente($lieu_vente)
                                            ->setPersonnel($this->getUser())
                                            ->setDateOperation($dateVente)
                                            ->setOrigine("liaison produit")
                                            ->setDescription("liaison produit")
                                            ->setClient($client);
                                $livraison->addMouvementProduct($mouvement_liaison_1);
                                $em->persist($mouvement_liaison_1);

                                $mouvement_liaison_2 = new MouvementProduct();
                                $mouvement_liaison_2->setStockProduct($stockLivraison)
                                            ->setProduct($product)
                                            ->setQuantite($product_liaison_1->getNbrePiece())
                                            ->setLieuVente($lieu_vente)
                                            ->setPersonnel($this->getUser())
                                            ->setDateOperation($dateVente)
                                            ->setOrigine("liaison produit")
                                            ->setDescription("liaison produit")
                                            ->setClient($client);
                                $livraison->addMouvementProduct($mouvement_liaison_2);
                                $em->persist($mouvement_liaison_2);

                                $stocks->setQuantite($stocks->getQuantite() + $product_liaison_1->getNbrePiece());
                                $em->persist($stocks);
                            }

                            if ($liaison->getType() == 'paquet') {
                                $stock_liaison_1->setQuantite($stock_liaison_1->getQuantite() - 1);
                                $em->persist($stock_liaison_1);

                                $mouvement_liaison_1 = new MouvementProduct();
                                $mouvement_liaison_1->setStockProduct($stockLivraison)
                                            ->setProduct($product_liaison_1)
                                            ->setQuantite(-1)
                                            ->setLieuVente($lieu_vente)
                                            ->setPersonnel($this->getUser())
                                            ->setDateOperation($dateVente)
                                            ->setOrigine("liaison produit")
                                            ->setDescription("liaison produit")
                                            ->setClient($client);
                                $livraison->addMouvementProduct($mouvement_liaison_1);
                                $em->persist($mouvement_liaison_1);

                                $mouvement_liaison_2 = new MouvementProduct();
                                $mouvement_liaison_2->setStockProduct($stockLivraison)
                                            ->setProduct($product)
                                            ->setQuantite($product_liaison_1->getNbrePaquet())
                                            ->setLieuVente($lieu_vente)
                                            ->setPersonnel($this->getUser())
                                            ->setDateOperation($dateVente)
                                            ->setOrigine("liaison produit")
                                            ->setDescription("liaison produit")
                                            ->setClient($client);
                                $livraison->addMouvementProduct($mouvement_liaison_2);
                                $em->persist($mouvement_liaison_2);

                                $stocks->setQuantite($stocks->getQuantite() + $product_liaison_1->getNbrePaquet());
                                $em->persist($stocks);
                            }

                        }
                    }

                }
                $em->persist($commande);
                if ($livraison) {
                    $em->persist($livraison);
                    $em->persist($mouvementProduct);
                }
            }            
            $em->flush();
            // on vide les sessions et on redirige vers la facture
            $session = $request->getSession();
            $session->remove('paiement');
            $session->remove('panier');
            $session->remove("session_client");
            $session->remove("session_client_com");
            $session->remove("session_client_com_montant");
            $session->remove("session_nom_client_cash");
            $session->remove("session_remise_glob");
            $session->remove("versement");
            $session->remove("cheque");
            $session->remove("frais_sup");
        }
        $id = $facturationRep->findMaxId();
        $this->addFlash("success", "Vente éffectuée avec succès :)"); 
        return $this->redirectToRoute("app_logescom_vente_facturation_show", ['id'=> $id, 'lieu_vente' => $lieu_vente->getId()]);
    } 

    #[Route('/delete/vente/{id}/{lieu_vente}', name: 'app_logescom_vente_facturation_delete_vente')]
    public function deleteVente($id, LieuxVentes $lieu_vente, Request $request, SessionInterface $session): Response
    {
        $panier = $session->get("panier", []);
        foreach ($panier as $indice => $item) {
            if ($item['stock']->getProducts()->getId()==$id) {
                unset($panier[$indice]);
                break;
            }
        }
        $session->set("panier", $panier);
        if (empty($panier)) {
            // si le panier est vide on initialise tous les paiements
            $session = $request->getSession();
            $session->remove('paiement');
            $session->remove("session_client_com");
            $session->remove("session_nom_client_cash");
            $session->remove("versement");
            $session->remove("cheque");
            $session->remove("frais_sup");            
        }
        $this->addFlash("success", "le produit a été retiré de votre panier"); 
        return $this->redirectToRoute("app_logescom_vente_facturation_vente", ['lieu_vente' => $lieu_vente->getId()]);
    }

    #[Route('/delete/vente/modification/{id}/{lieu_vente}/{facturation}', name: 'app_logescom_vente_facturation_delete_vente_modification')]
    public function deleteVenteModification($id, LieuxVentes $lieu_vente, Facturation $facturation, Request $request, SessionInterface $session): Response
    {
        $panier = $session->get("panier", []);
        foreach ($panier as $indice => $item) {
            if ($item['stock']->getProducts()->getId()==$id) {
                unset($panier[$indice]);
                break;
            }
        }
        $session->set("panier", $panier);
        if (empty($panier)) {
            // si le panier est vide on initialise tous les paiements
            $session = $request->getSession();
            $session->remove('paiement');
            $session->remove("session_client_com");
            $session->remove("session_nom_client_cash");
            $session->remove("versement");
            $session->remove("cheque");
            $session->remove("frais_sup");            
        }
        $this->addFlash("success", "le produit a été retiré de votre panier"); 

        return $this->redirectToRoute("app_logescom_vente_facturation_edit", ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()]);
    }
    
    /**
     * liste des factures
     */
    
    #[Route('/{lieu_vente}', name: 'app_logescom_vente_facturation_index')]
    public function facturations(LieuxVentes $lieu_vente, Request $request, UserRepository $userRep, AuthorizationCheckerInterface $authorizationChecker, FacturationRepository $facturationRep, EntrepriseRepository $entrepriseRep): Response
    {
        $session = $request->getSession();
        $session->remove('paiement');
        $session->remove('panier');
        $session->remove("session_client");
        $session->remove("session_client_com");
        $session->remove("session_client_com_montant");
        $session->remove("session_nom_client_cash");
        $session->remove("session_remise_glob");
        $session->remove("versement");
        $session->remove("cheque");
        $session->remove("frais_sup");

        
        if ($request->get("id_client_search")){
            $search = $request->get("id_client_search");
        }else{
            $search = "";
        }

        $session_date1 = $session->get('session_date1', date("Y-m-d"));
        $session_date2 = $session->get('session_date2', date("Y-m-d"));

        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

            $session_date1 = $date1;
            $session_date2 = $date2;

        }else{
            $date1 = date("Y-m-d");
            $date2 = date("Y-m-d");

            if (empty($session_date1)) {
                $session_date1 = $date1;
                $session_date2 = $date2;
            }
        }

        $session->set('session_date1', $session_date1);
        $session->set('session_date2', $session_date2);

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
            
            $facturations = $facturationRep->facturationParLieuParClientPaginated($lieu_vente, $search, $pageEncours, 50);
            $pagination = 'id_client_search';
        }elseif ($request->get("id_personnel")){
            $facturations = $facturationRep->findFacturationByLieuByPersonnelPaginated($lieu_vente, $request->get("id_personnel"), $session_date1, $session_date2, $pageEncours, 50);
            
            $pagination = 'id_personnel';

        }else{
            $pagination = '';

            if (!$authorizationChecker->isGranted('ROLE_GESTIONNAIRE')) {
                $facturations = $facturationRep->findFacturationByLieuByPersonnelPaginated($lieu_vente, $this->getUser(), $date1, $date2, $pageEncours, 50);
            }else{

                $facturations = $facturationRep->findFacturationByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 50);
            }
        }
        

        return $this->render('logescom/vente/facturation/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'facturations' => $facturations,
            'search' => $search,
            'pagination' => $pagination,
            'date1' => $date1,
            'date2' => $date2,
        ]);
    } 

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_vente_facturation_show')]
    public function show(Facturation $facturation, LieuxVentes $lieu_vente, MouvementCollaborateurRepository $mouvementCollabRep,CommandeProductRepository $commandeProdRep, MouvementCaisseRepository $mouvementCaisseRep, FactureFraisSupRepository $fraisRep, ModificationFactureRepository $modificationFactureRep, SessionInterface $session, Request $request, EntrepriseRepository $entrepriseRep): Response
    {
        $session = $request->getSession();
        $session->remove('paiement');
        $session->remove('panier');
        $session->remove("session_client");
        $session->remove("session_client_com");
        $session->remove("session_client_com_montant");
        $session->remove("session_nom_client_cash");
        $session->remove("session_remise_glob");
        $session->remove("versement");
        $session->remove("cheque");
        $session->remove("frais_sup");
        $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($facturation->getClient());
        return $this->render('logescom/vente/facturation/show.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'facturation' => $facturation,
            'commandes' => $commandeProdRep->findBy(['facturation' => $facturation], ['id' => 'ASC']),
            'caisses' => $mouvementCaisseRep->findBy(['facturation' => $facturation]),
            'frais_sups' => $fraisRep->findBy(['facturation' => $facturation]),
            'soldes_collaborateur' => $soldes_collaborateur,
            'modifications' => $modificationFactureRep->findBy(['facture' => $facturation])

        ]);
    } 

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_vente_facturation_edit')]
    public function edit(Facturation $facturation, LieuxVentes $lieu_vente, Request $request, ProductsRepository $productsRep, ProductsRepository $productRep, StockRepository $stockRep, UserRepository $userRep, MouvementCollaborateurRepository $mouvementCollabRep, DeviseRepository $deviseRep, TauxDeviseRepository $tauxDeviseRep, CaisseRepository $caisseRep, ModePaiementRepository $modePaieRep, ListeStockRepository $listeStockRep, MouvementCaisseRepository $mouvementCaisseRep, FraisSupRepository $fraisRep, CommandeProductRepository $commandeProdRep, FactureFraisSupRepository $factureFraisRep, CommissionVenteRepository $commissionRep, SessionInterface $session, EntrepriseRepository $entrepriseRep, FraisSupRepository $fraisSupRep, EntityManagerInterface $em): Response
    {
        $commission = $commissionRep->findOneBy(['id' => $facturation]);

        $session_client = $session->get("session_client", null);
        $session_client_com = $session->get("session_client_com", null);
        $session_client_com_montant = $session->get("session_client_com_montant", null);
        $session_nom_client_cash = $session->get("session_nom_client_cash", null);
        $session_remise_glob = $session->get("session_remise_glob", null);
        $versement = $session->get("versement", []);
        $cheque = $session->get("cheque", []);
        $frais_sup = $session->get("frais_sup", []);
        $panier = $session->get('panier', []);
        $paiement = $session->get('paiement', []);

        if ($request->get('modif_facture')) {
            $session = $request->getSession();
            $session->remove('paiement');
            $session->remove('panier');
            $session->remove("session_client");
            $session->remove("session_client_com");
            $session->remove("session_client_com_montant");
            $session->remove("session_nom_client_cash");
            $session->remove("session_remise_glob");
            $session->remove("versement");
            $session->remove("cheque");
            $session->remove("frais_sup");
        
            $mouvements_caisses = $mouvementCaisseRep->findBy(['facturation' => $facturation, 'typeMouvement' => 'facturation']);
            $frais_sup_facture = $factureFraisRep->findBy(['facturation' => $facturation]);

            $session_client = $session->get("session_client", $facturation->getClient());

            $session_client_com = $session->get("session_client_com", $commission ? $userRep->find($commission->getBeneficiaire()) : null);

            $session_client_com_montant = $session->get("session_client_com_montant", $commission ? $commission->getMontant() : null);

            $session_nom_client_cash = $session->get("session_nom_client_cash", $facturation->getNomClientCash());
            $session_remise_glob = $session->get("session_remise_glob", $facturation->getMontantRemise());

            foreach ($mouvements_caisses as  $value) {
                if ($value->getModePaie()->getId() == 1) {                
                    $paiement[$value->getDevise()->getNomDevise()] = ['montant' => $value->getMontant(), 'taux' => $value->getTaux()];               
                }elseif ($value->getModePaie()->getId() == 3) {
                    $versement =[
                        'montant' => $value->getMontant(), 
                        'banque' => $caisseRep->find($value->getCaisse()) 
                    ];
                }elseif ($value->getModePaie()->getId() == 4) {
                    $cheque =[
                        'montant' => $value->getMontant(),
                        'numero_cheque' => $value->getNumeroPaie(),
                        'banque' => $value->getBanqueCheque() 
                    ];
                }
            }
            foreach ($frais_sup_facture as $frais) {
                $frais_sup[] =[
                    'montant' => $frais->getMontant(), 
                    'type' => $frais->getFraisSup()
                ] ;
            }
            $versement = $session->get("versement", $versement);
            $cheque = $session->get("cheque", $cheque);
            $paiement = $session->get('paiement', $paiement);
            $frais_sup = $session->get("frais_sup", $frais_sup);
           

            // $panier = $session->get('panier', []);
            // $commandes = $commandeProdRep->findBy(['facturation' => $facturation]);
            // $stock_vente = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente, 'type' => 'vente']);
            // foreach ($commandes as $commande) {
            //     $stocks_lieu = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);
            //     $stock_dispo = $stockRep->sumQuantiteProduct($commande->getProduct(), $stocks_lieu);
            //     $stock_products = $stockRep->findOneBy(['product' => $commande->getProduct(), 'stockProduit' => $stock_vente]);

            //     $panier[]=["stock"=>$stock_products, "prixVente" => $commande->getPrixVente(), "qtite" => $commande->getQuantite(), 'remise' => $commande->getRemise(), 'dispo' => $stock_dispo ];
            // }

            $panier = $session->get('panier', []);
            $commandes = $commandeProdRep->findBy(['facturation' => $facturation]);
            $stock_vente = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente, 'type' => 'vente']);

            foreach ($commandes as $commande) {
                $stocks_lieu = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);
                $stock_dispo = $stockRep->sumQuantiteProduct($commande->getProduct(), $stocks_lieu);
                $stock_products = $stockRep->findOneBy(['product' => $commande->getProduct(), 'stockProduit' => $stock_vente]);

                $productId = $commande->getProduct()->getId();
                $quantite = $commande->getQuantite();
                $prixVente = $commande->getPrixVente();
                $remise = $commande->getRemise();

                // Vérifier si le produit existe déjà dans le panier
                $found = false;
                foreach ($panier as &$item) {
                    if ($item['stock']->getProducts()->getId() === $productId) {
                        // Si le produit est déjà dans le panier, on cumule les quantités
                        // et on met à jour le prix uniquement si le prix de vente est différent de 0
                        if ($prixVente != 0) {
                            $item['qtite'] += $quantite;
                            $item['prixVente'] = $prixVente;  // Met à jour le prix de vente si différent de 0
                            $item['remise'] = $remise;  // Met à jour la remise
                        } else {
                            // Si le prix est 0, on ne cumule que la quantité
                            $item['qtite'] += $quantite;
                        }
                        $found = true;
                        break;
                    }
                }

                // Si le produit n'est pas trouvé dans le panier, l'ajouter comme un nouvel élément
                if (!$found) {
                    $panier[] = [
                        'stock' => $stock_products,  // Stock du produit sélectionné
                        'prixVente' => $prixVente,  // Prix de vente du produit
                        'qtite' => $quantite,  // Quantité commandée
                        'remise' => $remise,  // Remise sur le produit
                        'dispo' => $stock_dispo  // Stock disponible
                    ];
                }
            }
            $session->set("session_client", $session_client);
            $session->set("session_nom_client_cash", $session_nom_client_cash);
            $session->set("session_client_com", $session_client_com);
            $session->set("session_client_com_montant", $session_client_com_montant);
            $session->set("session_remise_glob", $session_remise_glob);
            $session->set('versement', $versement);
            $session->set('cheque', $cheque);
            $session->set('paiement', $paiement);
            $session->set('frais_sup', $frais_sup);
            $session->set('panier', $panier);
            // return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_edit', ['id'=> $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()]));   
        }
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

            $search = $request->query->get('search_client');
            if ($search) {
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

            $search = $request->query->get('search_all_user');
            if ($search) {
                $clients = $userRep->findUserSearchByLieu($search, $lieu_vente);    
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

        if ($request->get("id_product_search")){
            $product = $request->get("id_product_search");
            $product = $productsRep->find($product);
            $stock_vente = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente, 'type' => 'vente']);
            $stock_product = $stockRep->findOneBy(['product' => $product, 'stockProduit' => $stock_vente]);

            $stocks_lieu = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);
            $stock_dispo = $stockRep->sumQuantiteProduct($product, $stocks_lieu);
        }else{
            $product = array();
            $stock_product = array();
        }

        if ($request->get("id_client_search")){
            $client = $request->get("id_client_search");
            $client = $userRep->find($client);
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client);
            $session_client= $client;            
        }else{
            $client = array();
            $soldes_collaborateur = array();
            if (empty($session_client)) {
                $session_client= array();
            }
        }

        if ($request->get("id_user")){
            $client_com = $request->get("id_user");
            $client_com = $userRep->find($client_com);
            $soldes_collaborateur_com = $mouvementCollabRep->findSoldeCollaborateur($client_com);
            $session_client_com= $client_com;            
        }else{
            $soldes_collaborateur_com = array();
            if ($commission) {
                $client_com = $userRep->find($session_client_com);
            }else{
                $client_com = null;
            }
            if (empty($session_client_com)) {
                $session_client_com= array();
            }
        }
        if ($request->get("nom_client_cash")){
            $session_nom_client_cash = $request->get("nom_client_cash");
        }else{
            if (empty($session_nom_client_cash)) {
                $session_nom_client_cash = array();
            }
        }
        
        if ($request->get("remise_glob")!== null){
            $montant_remise = floatval(preg_replace('/[^-0-9,.]/', '', $request->get("remise_glob")));
            $session_remise_glob = $montant_remise;
        }else{
            if (empty($session_remise_glob)) {
                $session_remise_glob = array();
            }
        }

        if ($request->get("versement_banque") !== null){
            $montantString = preg_replace('/[^-0-9,.]/', '', $request->get('versement_banque'));
            if (empty($versement)) {
                $versement =[
                    'montant' => floatval($montantString), 
                    'banque' => $caisseRep->find($request->get("caisse")) 
                ] ;
            }else{
                $versement =[
                    'montant' => floatval($montantString), 
                    'banque' => $caisseRep->find($request->get("caisse")) 
                ] ;
            }

            $session->set('versement', $versement);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_edit', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()]));  
        }

        if ($request->get("banque_cheque") !== null ){
            $montantString = preg_replace('/[^-0-9,.]/', '', $request->get('montant_cheque'));
            if (empty($cheque)) {
                $cheque =[
                    'montant' => floatval($montantString), 
                    'numero_cheque' => $request->get("numero_cheque"),
                    'banque' => $request->get("banque_cheque") 
                ] ;
            }else{
                $cheque =[
                    'montant' => floatval($montantString),
                    'numero_cheque' => $request->get("numero_cheque"),
                    'banque' => $request->get("banque_cheque") 
                ] ;
            }
            $session->set('cheque', $cheque);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_edit', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()]));
        }

        if ($request->get("montant_frais_sup") !== null){
            $montantString = floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montant_frais_sup')));
            if ($montantString !== null) {
                // Vérifier si le type existe déjà dans les paiements
                $fraisExistante = false;
                foreach ($frais_sup as $indice => $item) {
                    // Si le type existe déjà, mettre à jour le montant correspondant
                    if ($item['type'] == $request->get("type")) {
                        $frais_sup[$indice]['montant'] = $montantString;
                        $fraisExistante = true;
                        break;
                    }
                }
                // Si le type n'existe pas encore, l'ajouter
                if (!$fraisExistante) {
                    $frais_sup[] =[
                        'montant' => floatval($montantString), 
                        'type' => $request->get("type") 
                    ] ;
                }
            }
            $session->set('frais_sup', $frais_sup);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_edit', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()]));
        }

        $qtite = $request->query->get("qtite", 1);
        $prix_vente = $request->get('prix_vente', $stock_product ? $stock_product->getPrixVente() : 0);
        $remise = $request->get('remise', 0);
        $dejaAjouter = false;
        if ($request->get("id_product_search")){
            foreach ($panier as $indice => $item) {
                if ($item['stock']->getProducts()->getId() == $stock_product->getProducts()->getId()) {
                    if ($request->get('update')) {

                        $prixString = preg_replace('/[^-0-9,.]/', '', $request->get('prix_vente'));
                        $qtiteString = preg_replace('/[^-0-9,.]/', '', $request->get('qtite'));
                        $remiseString = preg_replace('/[^-0-9,.]/', '', $request->get('remise'));
                        $prixVenteUpdate = floatval($prixString);
                        $qtiteUpdate = floatval($qtiteString);
                        $remiseUpdate = floatval($remiseString);

                        $panier[$indice]['qtite'] = $qtiteUpdate;
                        $panier[$indice]['prixVente'] = $prixVenteUpdate ;
                        $panier[$indice]['remise'] = $remiseUpdate ;

                    }else{
                        $panier[$indice]['qtite'] += $qtite;
                    }
                    $item['client'] = $client;

                    $dejaAjouter= true;
                    break;
                }
            }
            if (!$dejaAjouter) {
                $panier[]=["stock"=>$stock_product, "prixVente" => $prix_vente, "qtite" => $qtite, 'remise' => $remise, 'dispo' => $stock_dispo ];
            }
            $session->set("panier", $panier);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_edit', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()]));
        }

        if ($request->get("id_devise")){
            $devises = $deviseRep->findAll();

            if (empty($paiement)) {
                foreach ($devises as $devise) {
                    $taux = $tauxDeviseRep->findOneBy(['devise' => $devise, 'lieuVente' => $lieu_vente]);
                    $taux = $taux ? $taux->getTaux() : 1;
                    $paiement[$devise->getNomDevise()] = ['montant' => 0, 'taux' => $taux];
                }
            }

            foreach ($devises as $key => $devise) {
                $montant = $request->get("montant_".$devise->getId());
                if (isset($montant)) {
                    $montant = floatval(preg_replace('/[^-0-9,.]/', '', $montant));
                    $taux = $tauxDeviseRep->findOneBy(['devise' => $devise, 'lieuVente' => $lieu_vente]);
                    $taux = $taux ? $taux->getTaux() : 1;
                    $paiement[$devise->getNomDevise()] = ['montant' => $montant, 'taux' => $taux];
                }
            }

            $session->set("paiement", $paiement);
            return new RedirectResponse($this->generateUrl('app_logescom_vente_facturation_edit', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()]));
        }
        if ($request->get('id_taux')) {
            $taux_devise = $tauxDeviseRep->findBy(['lieuVente' => $lieu_vente]);
            foreach ($taux_devise as $key => $taux) {
                $taux = $request->get($taux->getId());
                $taux = floatval(preg_replace('/[^-0-9,.]/', '', $taux));

                if ($taux) {
                    $taux_devise = $tauxDeviseRep->find($request->get('id_taux'));
                    $taux_devise->setTaux($taux);
                    $em->persist($taux_devise);
                    $em->flush();
                }
            }
        }

        
        $session->set("session_client", $session_client);
        $session->set("session_nom_client_cash", $session_nom_client_cash);
        $session->set("session_client_com", $session_client_com);
        $session->set("session_remise_glob", $session_remise_glob);
        $session->set('versement', $versement);

        $nbre_en_gros = 0;
        $nbre_paquet = 0;
        $nbre_detail = 0;
        foreach ($panier as $valueCount) {
            if ($valueCount['stock']->getProducts()->getTypeProduit() == 'detail') {
                $nbre_detail += $valueCount['qtite'];
            }elseif ($valueCount['stock']->getProducts()->getTypeProduit() == 'paquet') {
                $nbre_paquet += $valueCount['qtite'];
            }else{
                $nbre_en_gros += $valueCount['qtite'];
            }

        }
        // dd($panier);
        return $this->render('logescom/vente/facturation/vente_modification.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search_product' => $product,
            'search_client' => $client,
            'client_com' => $client_com,
            'soldes_collaborateur' => $soldes_collaborateur,
            'devises' => $deviseRep->findAll(),
            'taux_devise' => $tauxDeviseRep->findBy(['lieuVente' => $lieu_vente]),
            'modes_paie' => $modePaieRep->findAll(),
            'frais_sups' => $fraisSupRep->findAll(),
            'caisses_banque' => $caisseRep->findCaisseByLieuByType($lieu_vente, 'banque'), 
            'caisses' => $caisseRep->findCaisseByLieu($lieu_vente), 
            'liste_stocks' => $listeStockRep->findBy(['lieuVente' => $lieu_vente]),
            'panier' => $panier,
            'session_client' => $session_client,
            'session_client_com' => $session_client_com,
            'session_client_com_montant' => $session_client_com_montant,
            'session_nom_client_cash' => $session_nom_client_cash,
            'session_remise_glob' => $session_remise_glob,
            'paiements' => $paiement,
            'versement' => $versement,
            'cheque' => $cheque,
            'frais' => $frais_sup,
            'facture' => $facturation,
            'nbre_en_gros' => $nbre_en_gros,
            'nbre_paquet' => $nbre_paquet,
            'nbre_detail' => $nbre_detail,
        ]);
    }

    #[Route('/produit/vente/{lieu_vente}', name: 'app_logescom_vente_facturation_produit_vente', methods: ['GET'])]
    public function venteProduit(LieuxVentes $lieu_vente, Request $request, EntrepriseRepository $entrepriseRep, LivraisonRepository $livraisonRep, CommandeProductRepository $commandeProdRep, EntityManagerInterface $em): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");
        }else{
            $date1 = date("Y-m-d");
            $date2 = date("Y-m-d");
        }

        $commandes_groupes = $commandeProdRep->listeDesProduitsVendusGroupeParPeriodeParLieu($date1, $date2, $lieu_vente);

        $livraisons_groupes = $livraisonRep->listeDesProduitsLivresGroupeParPeriodeParLieu($date1, $date2, $lieu_vente);

        $pageEncours = $request->get('pageEncours', 1);
        $commandes = $commandeProdRep->listeDesProduitsVendusParPeriodeParLieuPagine($date1, $date2, $lieu_vente, $pageEncours, 30);
        $pageEncoursLiv = $request->get('pageEncoursLiv', 1);
        $livraisons = $livraisonRep->listeDesProduitsLivresParPeriodeParLieuPagine($date1, $date2, $lieu_vente, $pageEncoursLiv, 30);

        return $this->render('logescom/vente/facturation/produits_vendus.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'commandes_groupes' => $commandes_groupes,
            'commandes' => $commandes,
            'livraisons' => $livraisons,
            'livraisons_groupes' => $livraisons_groupes,
            'date1' => $date1,
            'date2' => $date2,
            'search' => ""
        ]);
    }

    #[Route('/modification/valdation/{facture}/{lieu_vente}', name: 'app_logescom_vente_facturation_validation_modification')]
    public function validationModification(Facturation $facture, LieuxVentes $lieu_vente, Request $request, FacturationRepository $facturationRep, StockRepository $stockRep, ListeStockRepository $listeStockRep, CaisseRepository $caisseRep, DeviseRepository $deviseRep, FraisSupRepository $fraisSupRep, CategorieOperationRepository $categorieOpRep, CompteOperationRepository $compteOpRep,  SessionInterface $session, ModePaiementRepository $modePaieRep, UserRepository $userRep, CommandeProductRepository $commandeProdRep, LivraisonRepository $livraisonRep, MouvementProductRepository $mouvementProdRep, CommissionVenteRepository $commissionRep, FactureFraisSupRepository $factureFraisRep, ProductsRepository $productRep, MouvementCollaborateurRepository $mouvementCollabRep, LiaisonProduitRepository $liaisonRep, MouvementCaisseRepository $mouvementCaisseRep, PromotionRepository $promotionRep, EntityManagerInterface $em): Response
    {
        $session_client = $session->get("session_client", '');
        $session_client_com = $session->get("session_client_com", '');
        $session_nom_client_cash = $session->get("session_nom_client_cash", '');
        $session_remise_glob = $session->get("session_remise_glob", '');
        $versement = $session->get("versement", '');
        $cheque = $session->get("cheque", '');
        $frais_sups = $session->get("frais_sup", '');
        $panier = $session->get("panier", []);
        $paiements = $session->get("paiement", []);

        if ($panier) {
            $devise = $deviseRep->find(1);
            $init = $lieu_vente->getInitial();
            $dateDuJour = new \DateTime();
            $numeroFacture = $facture->getNumeroFacture();

            $totalFacture =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('totalFacture')));
            $totalFacture = $totalFacture ? $totalFacture : 0;

            $montantPaye =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montantPaye')));
            $montantPaye = $montantPaye ? $montantPaye : 0;

            $reste =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('reste')));
            $reste = $reste ? $reste : 0;

            $rendu =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('rendu')));
            $rendu = $rendu ? $rendu : 0;

            $montantRemise =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montantRemise')));

            $frais =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('frais')));
            $frais = $frais ? $frais : 0 ;

            $montant_com =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montant_com')));
            $montant_com = $montant_com ? $montant_com : 0 ;

            $livraison = $request->get('livraison');
            $stockLivraison = $livraison ? $listeStockRep->find($livraison) : null;

            $caisse = $request->get('caisse') ? $caisseRep->find($request->get('caisse')) : null;

            $dateVente = $request->get('date_vente') ? (new \DateTime($request->get('date_vente'))) : $dateDuJour ;

            $dateAlerte = $request->get('date_alerte') ? (new \DateTime($request->get('date_alerte'))) : null ;

            $commentaire = $request->get('commentaire') ? ($request->get('commentaire')) : null ;

            $client = $session_client ? $userRep->find($session_client->getId()) : null;

            // preparation de la facture
            if ($montantPaye > $totalFacture) {
                // gestion dans le cas il y a un rendu
                $montantPaye = $totalFacture;
            }

            // on supprime l'ancienne commande et mis à jour du stock
            $commandes = $commandeProdRep->findBy(['facturation' => $facture]);
            $livraisons = $livraisonRep->findBy(['commande' => $commandes]);
            $mouvementsProduct = $mouvementProdRep->findBy(['livraison' => $livraisons]);
            foreach ($mouvementsProduct as $mouvementProd) {
                $stock_maj = $stockRep->findOneBy(['stockProduit' => $mouvementProd->getStockProduct(), 'product' => $mouvementProd->getProduct()]);
                $stock_maj->setQuantite($stock_maj->getQuantite() - ($mouvementProd->getQuantite()));
                $em->persist($stock_maj);

            }
            foreach ($commandes as $commande) {
                // on supprime la commande et automatiquement ça supprime la livraiosn et le mouvement des produits
                $em->remove($commande);

            }


            // on stock la modification dans la table historique des modifictaions
            $modificationFacture = new ModificationFacture();
            $modificationFacture->setFacture($facture)
                    ->setTotalFacture($facture->getTotalFacture())
                    ->setMontantPaye($facture->getMontantPaye())
                    ->setMontantRemise($facture->getMontantRemise())
                    ->setFraisSup($facture->getFraisSup())
                    ->setClient($facture->getClient())
                    ->setEtat($facture->getEtat())
                    ->setEtatLivraison($facture->getEtatLivraison())
                    ->setNomClientCash($facture->getNomClientCash())
                    ->setCaisse($facture->getCaisse())
                    ->setModePaie($facture->getModePaie())
                    ->setLieuVente($lieu_vente)
                    ->setSaisiePar($this->getUser())
                    ->setDateFacturation($facture->getDateFacturation())
                    ->setDateSaisie(new \DateTime("now"));
            $em->persist($modificationFacture); 

            $factures_frais = $factureFraisRep->findBy(['facturation' => $facture]);
            foreach ($factures_frais as $facture_frais) {
                $em->remove($facture_frais);

            }
            $commissions = $commissionRep->findOneBy(['facture' => $facture]);
            if ($commissions) {
                $em->remove($commissions);

            }

            $mouv_collab = $mouvementCollabRep->findOneBy(['facture' => $facture]);
            if ($mouv_collab) {
                $em->remove($mouv_collab);

            }

            $mouv_caisse = $mouvementCaisseRep->findBy(['facturation' => $facture]);
            if ($mouv_caisse) {
                foreach ($mouv_caisse as $mouv) {
                    $em->remove($mouv);
                }
            }
            // dd($facture);
            $facture->setNumeroFacture($numeroFacture)
                    ->setTotalFacture($totalFacture)
                    ->setMontantPaye($montantPaye)
                    ->setMontantRemise($montantRemise)
                    ->setFraisSup($frais)
                    ->setClient($client)
                    ->setEtat(($totalFacture == $montantPaye) ? "payé" : ($montantPaye ? 'paie partiel' : 'non payé'))
                    ->setEtatLivraison($livraison ? "livré" : "non livré")
                    ->setCaisse($caisse)
                    ->setModePaie($modePaieRep->find(1))
                    ->setLieuVente($lieu_vente)
                    ->setSaisiePar($this->getUser())
                    ->setDateFacturation($dateVente)
                    ->setDateSaisie(new \DateTime('now'))
                    ->setDateAlerte($dateAlerte)
                    ->setNomClientCash($session_nom_client_cash ? $session_nom_client_cash : null)
                    ->setCommentaire($commentaire);
            $em->persist($facture);
            // enregistrement des frais sup
            if ($frais) {
                foreach ($frais_sups as $frais_sup) {
                    if ($frais_sup['type']) {
                        $type = $fraisSupRep->find($frais_sup['type']);
                        $factureFrais = new FactureFraisSup();
                        $factureFrais->setMontant($frais_sup['montant'])
                                ->setDevise($devise)
                                ->setFraisSup($type);
                        $facture->addFactureFraisSup($factureFrais);
                        $em->persist($factureFrais);

                        
                    }
                }

                $mouvementCaisseFrais = new MouvementCaisse();
                $mouvementCaisseFrais->setLieuVente($lieu_vente)
                        ->setCaisse($caisse)
                        ->setModePaie($modePaieRep->find(1))
                        ->setCategorieOperation($categorieOpRep->find(1))
                        ->setCompteOperation($compteOpRep->find(6))
                        ->setTypeMouvement('frais sup')
                        ->setMontant(-$frais)
                        ->setDevise($devise)
                        ->setTaux(1)
                        ->setDateOperation($facture->getDateFacturation())
                        ->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));
                $facture->addMouvementCaiss($mouvementCaisseFrais);
                $em->persist($mouvementCaisseFrais);
            }

            if ($request->get("id_client_com")) {
                $commission = new CommissionVente();
                $client_com = $userRep->find($request->get("id_client_com"));
                $commission->setBeneficiaire($client_com)
                        ->setMontant($montant_com)
                        ->setDatePaiement($dateVente);
                $facture->addCommissionVente($commission);
                $em->persist($commission);

                if ($reste == 0) {
                    // si la facture est payée on credite le compte du bénéficiaire
                    $mouvementCollabCom = new MouvementCollaborateur ();
                    $mouvementCollabCom->setCollaborateur($client_com)
                            ->setOrigine('commission')
                            ->setMontant($montant_com)
                            ->setDevise($devise)
                            ->setCaisse($caisse)
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser())
                            ->setDateOperation($facture->getDateFacturation())
                            ->setDateSaisie(new \DateTime('now'));
                    $facture->addMouvementCollaborateur($mouvementCollabCom);
                    $em->persist($mouvementCollabCom);
                }
            }

            // enregistrement dans le mouvement caisse

            if ($montantPaye) {
                // paiement espèces
                if ($paiements) {
                    foreach ($paiements as $key => $paiement) {
                        $devisePaie = $deviseRep->findOneBy(['nomDevise' => $key ]);
                        $montant = $paiement['montant'];
                        // gerer gestion de rendu à ce niveau 
                        if ($montant > $montantPaye) {
                            $montant = $totalFacture;
                        }
                        if ($montant) {
                            $mouvementCaisseEspeces = new MouvementCaisse();
                            $mouvementCaisseEspeces->setLieuVente($lieu_vente)
                                    ->setCaisse($caisse)
                                    ->setModePaie($modePaieRep->find(1))
                                    ->setCategorieOperation($categorieOpRep->find(1))
                                    ->setCompteOperation($compteOpRep->find(6))
                                    ->setTypeMouvement('facturation')
                                    ->setMontant($montant)
                                    ->setDevise($devisePaie)
                                    ->setDateOperation($facture->getDateFacturation())
                                    ->setSaisiePar($this->getUser())
                                    ->setDateSaisie(new \DateTime("now"));
                            $facture->addMouvementCaiss($mouvementCaisseEspeces);
                            $em->persist($mouvementCaisseEspeces);
                        }

                    }
                }

                // paiement par cheque
                if ($cheque) {
                    $mouvementCaisseCheque = new MouvementCaisse();
                    $mouvementCaisseCheque->setLieuVente($lieu_vente)
                            ->setCaisse($caisse)
                            ->setModePaie($modePaieRep->find(4))
                            ->setNumeroPaie($cheque['numero_cheque'])
                            ->setBanqueCheque($cheque['banque'])
                            ->setCategorieOperation($categorieOpRep->find(1))
                            ->setCompteOperation($compteOpRep->find(6))
                            ->setTypeMouvement('facturation')
                            ->setMontant($cheque['montant'])
                            ->setDevise($devise)
                            ->setDateOperation($facture->getDateFacturation())
                            ->setSaisiePar($this->getUser())
                            ->setDateSaisie(new \DateTime("now"));
                    $facture->addMouvementCaiss($mouvementCaisseCheque);
                    $em->persist($mouvementCaisseCheque);

                }

                // paiement par versement
                if ($versement) {
                    $banque = $caisseRep->find($versement['banque']);
                    $mouvementCaisseVersement = new MouvementCaisse();
                    $mouvementCaisseVersement->setLieuVente($lieu_vente)
                            ->setCaisse($banque)
                            ->setModePaie($modePaieRep->find(3))
                            ->setBanqueCheque($versement['banque']->getDesignation())
                            ->setCategorieOperation($categorieOpRep->find(1))
                            ->setCompteOperation($compteOpRep->find(6))
                            ->setTypeMouvement('facturation')
                            ->setMontant($versement['montant'])
                            ->setDevise($devise)
                            ->setDateOperation($facture->getDateFacturation())
                            ->setSaisiePar($this->getUser())
                            ->setDateSaisie(new \DateTime("now"));
                    $facture->addMouvementCaiss($mouvementCaisseVersement);
                    $em->persist($mouvementCaisseVersement);

                }
            }

            // enregistrement dans le compte du collaborateur s'il n y a pas de paiement
            if ($session_client) {
                $mouvementCollab = new MouvementCollaborateur ();
                $mouvementCollab->setCollaborateur($client)
                        ->setOrigine('facturation')
                        ->setDevise($devise)
                        ->setCaisse($caisse)
                        ->setLieuVente($lieu_vente)
                        ->setTraitePar($this->getUser())
                        ->setDateOperation($facture->getDateFacturation())
                        ->setDateSaisie(new \DateTime('now'));
                    $facture->addMouvementCollaborateur($mouvementCollab);
                if ($rendu >= 0) {                
                    $mouvementCollab->setMontant(-$rendu);
                }else{
                    $mouvementCollab->setMontant(0); 
                }
                $em->persist($mouvementCollab);
            }

            $bonusApplies = []; // Pour stocker les bonus déjà appliqués pour éviter les doublons

            foreach ($panier as $item) {
                $product = $productRep->find($item['stock']->getProducts()->getId());
            
                // Trouver les promotions associées à ce produit
                $promotions = $promotionRep->findPromotionByProduct($product);
            
                foreach ($promotions as $promotion) {
                    $totalQuantity = 0;
                    $promoProductsIds = [];
            
                    // Calculer la somme des quantités des produits du panier présents dans la promotion
                    foreach ($promotion->getProduits() as $promoProduct) {
                        foreach ($panier as $cartItem) {
                            if ($cartItem['stock']->getProducts()->getId() === $promoProduct->getId()) {
                                $totalQuantity += $cartItem['qtite'];
                                $promoProductsIds[] = $promoProduct->getId(); // Collecter les IDs des produits liés à la promo
                            }
                        }
                    }
            
                    // Créer une clé unique pour les produits liés à la promotion
                    sort($promoProductsIds); // Trier les IDs pour assurer la cohérence
                    $bonusKey = $promotion->getId() . '_' . implode('_', $promoProductsIds);
            
                    // Si la somme des quantités est supérieure ou égale à la quantité minimale requise
                    if ($totalQuantity >= $promotion->getQuantiteMin() && !isset($bonusApplies[$bonusKey])) {
                        // Calculer le facteur de la quantité bonus
                        $factor = floor($totalQuantity / $promotion->getQuantiteMin());
            
                        // Initialiser la quantité bonus en fonction du facteur
                        $quantiteOfferte = $factor * $promotion->getQuantiteBonus();
                        if ($factor >= 4 && $factor < 8) {
                            $quantiteOfferte += $promotion->getQuantiteBonus();
                        } elseif ($factor >= 8 && $factor < 9) {
                            $quantiteOfferte += 2 * $promotion->getQuantiteBonus() + 1;
                        }elseif ($factor >= 9 && $factor < 10) {
                            $quantiteOfferte += 2 * $promotion->getQuantiteBonus() + 1;
                        }elseif ($factor >= 10) {
                            $quantiteOfferte += 2 * $promotion->getQuantiteBonus() + 1.5;
                        }
            
                        // Trouver le produit du panier avec la plus grande quantité et le plus grand stock disponible parmi les produits liés
                        $maxQuantityItem = null;
                        foreach ($panier as $cartItem) {
                            if (in_array($cartItem['stock']->getProducts()->getId(), $promoProductsIds)) {
                                if (!$maxQuantityItem || $cartItem['qtite'] > $maxQuantityItem['qtite']) {
                                    $maxQuantityItem = $cartItem;
                                }
                            }
                        }
            
                        // Vérifier si le produit bonus existe déjà dans le panier
                        $bonusExists = false;
                        foreach ($panier as $cartItem) {
                            if ($cartItem['stock']->getProducts()->getId() === $maxQuantityItem['stock']->getProducts()->getId() && $cartItem['prixVente'] == 0) {
                                $bonusExists = true;
                                break;
                            }
                        }
            
                        // Si le produit bonus n'est pas déjà dans le panier, l'ajouter
                        if (!$bonusExists) {
                            $panier[] = [
                                'stock' => $maxQuantityItem['stock'],  // Stock du produit sélectionné
                                'prixVente' => 0,  // Prix de la promotion (0 pour le bonus)
                                'qtite' => $quantiteOfferte,  // Quantité bonus calculée
                                'remise' => 0,  // Gérer la remise si nécessaire
                                'dispo' => null  // Autre information de disponibilité si nécessaire
                            ];
                        }
            
                        // Marquer la promotion comme appliquée pour cet ensemble de produits
                        $bonusApplies[$bonusKey] = true;
                    }
                }
            }

            // insertion des produits dans la bdd
            foreach ($panier as  $item) {
                $commande = new CommandeProduct();
                $product = $productRep->find($item['stock']->getProducts()->getId());
                $commande->setProduct($product)
                        ->setPrixVente($item['prixVente'])
                        ->setPrixAchat($item['stock']->getPrixAchat())
                        ->setPrixRevient($item['stock']->getPrixRevient())
                        ->setRemise($item['remise'])
                        ->setTva($product->getTva())
                        ->setQuantite($item['qtite']);
                $facture->addCommandeProduct($commande);
                if ($request->get('livraison')) {
                    $commande->setQuantiteLivre($item['qtite']);
                    $livraison = new Livraison();
                    $livraison->setQuantiteLiv($item['qtite'])
                            ->setSaisiePar($this->getUser())
                            ->setStock($stockLivraison)
                            ->setCommentaire("livraison direct")
                            ->setDateLivraison($facture->getDateFacturation())
                            ->setDateSaisie(new \DateTime("now"));
                    $commande->addLivraison($livraison);
                    // on met à jour le stock
                    $stocks = $stockRep->findOneBy(['stockProduit' => $stockLivraison, 'product' => $item['stock']->getProducts()]);
                    $stocks->setQuantite(($stocks->getQuantite() - $item['qtite']));
                    
                    // on insere dans le mouvement product
                    $mouvementProduct = new MouvementProduct();
                    $mouvementProduct->setStockProduct($stockLivraison)
                                ->setProduct($product)
                                ->setQuantite(-$item['qtite'])
                                ->setLieuVente($lieu_vente)
                                ->setPersonnel($this->getUser())
                                ->setDateOperation($facture->getDateFacturation())
                                ->setOrigine("vente direct")
                                ->setDescription("vente direct")
                                ->setClient($client);
                    $livraison->addMouvementProduct($mouvementProduct);

                    // on gère la liaison des produits
                    if ($stocks->getQuantite() <= 0) {
                        $liaison = $liaisonRep->findOneBy(['produit2' => $product]);
                        if ($liaison) {
                            $product_liaison_1 = $liaison->getProduit1();
    
                            $stock_liaison_1 = $stockRep->findOneBy(['stockProduit' => $stockLivraison, 'product' => $product_liaison_1]);
                            if ($liaison->getType() == 'detail') {
                                $stock_liaison_1->setQuantite($stock_liaison_1->getQuantite() - 1);
                                $em->persist($stock_liaison_1);
        
                                $mouvement_liaison_1 = new MouvementProduct();
                                $mouvement_liaison_1->setStockProduct($stockLivraison)
                                            ->setProduct($product_liaison_1)
                                            ->setQuantite(-1)
                                            ->setLieuVente($lieu_vente)
                                            ->setPersonnel($this->getUser())
                                            ->setDateOperation($facture->getDateFacturation())
                                            ->setOrigine("liaison produit")
                                            ->setDescription("liaison produit")
                                            ->setClient($client);
                                $livraison->addMouvementProduct($mouvement_liaison_1);
                                $em->persist($mouvement_liaison_1);
        
                                $mouvement_liaison_2 = new MouvementProduct();
                                $mouvement_liaison_2->setStockProduct($stockLivraison)
                                            ->setProduct($product)
                                            ->setQuantite($product_liaison_1->getNbrePiece())
                                            ->setLieuVente($lieu_vente)
                                            ->setPersonnel($this->getUser())
                                            ->setDateOperation($facture->getDateFacturation())
                                            ->setOrigine("liaison produit")
                                            ->setDescription("liaison produit")
                                            ->setClient($client);
                                $livraison->addMouvementProduct($mouvement_liaison_2);
                                $em->persist($mouvement_liaison_2);
        
                                $stocks->setQuantite($stocks->getQuantite() + $product_liaison_1->getNbrePiece());
                                $em->persist($stocks);
                            }

                            if ($liaison->getType() == 'paquet') {
                                $stock_liaison_1->setQuantite($stock_liaison_1->getQuantite() - 1);
                                $em->persist($stock_liaison_1);
        
                                $mouvement_liaison_1 = new MouvementProduct();
                                $mouvement_liaison_1->setStockProduct($stockLivraison)
                                            ->setProduct($product_liaison_1)
                                            ->setQuantite(-1)
                                            ->setLieuVente($lieu_vente)
                                            ->setPersonnel($this->getUser())
                                            ->setDateOperation($facture->getDateFacturation())
                                            ->setOrigine("liaison produit")
                                            ->setDescription("liaison produit")
                                            ->setClient($client);
                                $livraison->addMouvementProduct($mouvement_liaison_1);
                                $em->persist($mouvement_liaison_1);
        
                                $mouvement_liaison_2 = new MouvementProduct();
                                $mouvement_liaison_2->setStockProduct($stockLivraison)
                                            ->setProduct($product)
                                            ->setQuantite($product_liaison_1->getNbrePaquet())
                                            ->setLieuVente($lieu_vente)
                                            ->setPersonnel($this->getUser())
                                            ->setDateOperation($facture->getDateFacturation())
                                            ->setOrigine("liaison produit")
                                            ->setDescription("liaison produit")
                                            ->setClient($client);
                                $livraison->addMouvementProduct($mouvement_liaison_2);
                                $em->persist($mouvement_liaison_2);
        
                                $stocks->setQuantite($stocks->getQuantite() + $product_liaison_1->getNbrePaquet());
                                $em->persist($stocks);
                            }
                        }
                    }
                }
                $em->persist($commande);
                if ($livraison) {
                    $em->persist($livraison);
                    $em->persist($mouvementProduct);
                }
            }
            

            $em->flush();
            // on vide les sessions et on redirige vers la facture
            $session = $request->getSession();
            $session->remove('paiement');
            $session->remove('panier');
            $session->remove("session_client");
            $session->remove("session_client_com");
            $session->remove("session_client_com_montant");
            $session->remove("session_nom_client_cash");
            $session->remove("session_remise_glob");
            $session->remove("versement");
            $session->remove("cheque");
            $session->remove("frais_sup");


        }
        $this->addFlash("success", "Vente éffectuée avec succès :)"); 
        return $this->redirectToRoute("app_logescom_vente_facturation_show", ['id'=> $facture->getId(), 'lieu_vente' => $lieu_vente->getId()]);
    }

    #[Route('/facture/{id}/{lieu_vente}', name: 'app_logescom_vente_facturation_facture')]
    public function facture(Facturation $facturation, LieuxVentes $lieu_vente, CaisseRepository $caisseRep, ClientRepository $clientRep, CommandeProductRepository $commandeProdRep, MouvementCaisseRepository $mouvementCaisseRep, MouvementCollaborateurRepository $mouvementCollabRep, ConfigurationLogicielRepository $configRep, PersonnelRepository $personnelRep)
    {
        
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$lieu_vente->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $commandes = $commandeProdRep->findAllCommand($facturation);
        // dd($commandes);

        $soldes = $mouvementCollabRep->findSoldeCollaborateur($facturation->getClient());
        $soldes_date = $mouvementCollabRep->findAncienSoldeCollaborateur($facturation->getClient(), $facturation->getDateFacturation());
        $personnel = $personnelRep->findOneBy(['user' => $facturation->getSaisiePar()]);

        if ($personnel) {
            
            if ($personnel->getSignature()) {
                $signaturePath = $this->getParameter('kernel.project_dir') . '/public/personnel/identite/'.$personnel->getSignature();
                $signatureBase64 = base64_encode(file_get_contents($signaturePath));
            }else{
                $signatureBase64 = "";
            }
        }else{
            $signatureBase64 = "";

        }
        $html = $this->renderView('logescom/vente/facturation/facture.html.twig', [
            'facturation' => $facturation,
            'commandes' => $commandes,
            'mouvements_caisse' => $mouvementCaisseRep->mouvementFacturationNotFrais($facturation), 
            'client' => $clientRep->findOneBy(['user' => $facturation->getClient()]),
            'personnel' => $personnel,
            'signature' => $signatureBase64,
            'soldes' => $soldes,
            'soldes_date' => $soldes_date,
            'logoPath' => $logoBase64,
            'lieu_vente' => $lieu_vente,
            'caisses_banque' => $caisseRep->findCaisseDocumentByLieuByType($lieu_vente, 'banque'), 
            'config' => $configRep->findOneBy([]),
            // 'qrCode'    => $qrCode,
        ]);

        // return $this->render('logescom/vente/facturation/facture.html.twig', [
        //     'facturation' => $facturation,
        //     'commandes' => $commandes,
        //     'mouvements_caisse' => $mouvementCaisseRep->mouvementFacturationNotFrais($facturation), 
        //     'client' => $clientRep->findOneBy(['user' => $facturation->getClient()]),
        //     'personnel' => $personnel,
        //     'signature' => $signatureBase64,
        //     'soldes' => $soldes,
        //     'soldes_date' => $soldes_date,
        //     'logoPath' => $logoBase64,
        //     'lieu_vente' => $lieu_vente,
        //     'caisses_banque' => $caisseRep->findCaisseDocumentByLieuByType($lieu_vente, 'banque'), 
        //     'config' => $configRep->findOneBy([]),
        // ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set("isPhpEnabled", true);
        $options->set("isHtml5ParserEnabled", true);

        // Instancier Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier (A4 par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF (stream le PDF au navigateur)
        $dompdf->render();

        // Renvoyer une réponse avec le contenu du PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename='.$facturation->getNumeroFacture().'_facture.pdf',
        ]);
    }

    #[Route('/facture/petit/{id}/{lieu_vente}', name: 'app_logescom_vente_facturation_facture_petit')]
    public function facturePetitFormat(Facturation $facturation, LieuxVentes $lieu_vente, CaisseRepository $caisseRep, ClientRepository $clientRep, CommandeProductRepository $commandeProdRep, MouvementCaisseRepository $mouvementCaisseRep, MouvementCollaborateurRepository $mouvementCollabRep)
    {
        
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$lieu_vente->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $commandes = $commandeProdRep->findBy(['facturation' => $facturation], ['id' => 'ASC']);

        // $soldes = $mouvementCollabRep->findSoldeCollaborateur($facturation->getClient());
        // $soldes_date = $mouvementCollabRep->findAncienSoldeCollaborateur($facturation->getClient(), $facturation->getDateFacturation());

        $html = $this->renderView('logescom/vente/facturation/facture_petit_format.html.twig', [
            'commandes' => $commandes,
            'logoPath' => $logoBase64,
            'lieu_vente' => $lieu_vente,
            'mouvements_caisse' => $mouvementCaisseRep->mouvementFacturationNotFrais($facturation), 
            'facture' => $facturation,
        ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set("isPhpEnabled", true);
        $options->set("isHtml5ParserEnabled", true);

        // Instancier Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier (A4 par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF (stream le PDF au navigateur)
        $dompdf->render();

        // Renvoyer une réponse avec le contenu du PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename='.$facturation->getNumeroFacture().'"facture-petit.pdf"',
        ]);
    }

    #[Route('/bon/livraison/{id}/{lieu_vente}', name: 'app_logescom_vente_facturation_bon_livraison')]
    public function bonLivraison(Facturation $facturation, ConfigurationLogicielRepository $configRep, LieuxVentes $lieu_vente, CaisseRepository $caisseRep, ClientRepository $clientRep, CommandeProductRepository $commandeProdRep, MouvementCaisseRepository $mouvementCaisseRep, MouvementCollaborateurRepository $mouvementCollabRep)
    {        
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$lieu_vente->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $commandes = $commandeProdRep->findAllCommand($facturation);
        // dd($commandes);

        $soldes = $mouvementCollabRep->findSoldeCollaborateur($facturation->getClient());
        $soldes_date = $mouvementCollabRep->findAncienSoldeCollaborateur($facturation->getClient(), $facturation->getDateFacturation());

        $html = $this->renderView('logescom/vente/facturation/bon_livraison.html.twig', [
            'facturation' => $facturation,
            'commandes' => $commandes,
            'mouvements_caisse' => $mouvementCaisseRep->findBy(['facturation' => $facturation]), 
            'client' => $clientRep->findOneBy(['user' => $facturation->getClient()]),
            'soldes' => $soldes,
            'soldes_date' => $soldes_date,
            'logoPath' => $logoBase64,
            'lieu_vente' => $lieu_vente,
            'caisses_banque' => $caisseRep->findCaisseDocumentByLieuByType($lieu_vente, 'banque'), 
            'config' => $configRep->findOneBy([]), 
            // 'qrCode'    => $qrCode,
        ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set("isPhpEnabled", true);
        $options->set("isHtml5ParserEnabled", true);

        // Instancier Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier (A4 par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF (stream le PDF au navigateur)
        $dompdf->render();

        // Renvoyer une réponse avec le contenu du PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename='.$facturation->getNumeroFacture().'"bon-livraison.pdf"',
        ]);
    }

    

    #[Route('/prepa/commande/{id}/{lieu_vente}', name: 'app_logescom_vente_facturation_prepa_commande1')]
    public function prepaCommande1(Facturation $facturation, ConfigurationLogicielRepository $configRep, LieuxVentes $lieu_vente, CaisseRepository $caisseRep, ClientRepository $clientRep, CommandeProductRepository $commandeProdRep, ListeStockRepository $listeStockRep)
    {        
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$lieu_vente->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $commandes = $commandeProdRep->findBy(['facturation' => $facturation]);
        $listeStocks = $listeStockRep->findAll();

        $html = $this->renderView('logescom/vente/facturation/prepa_commande1.html.twig', [
            'facturation' => $facturation,
            'commandes' => $commandes,
            'listeStocks' => $listeStocks,
            'client' => $clientRep->findOneBy(['user' => $facturation->getClient()]),
            'logoPath' => $logoBase64,
            'lieu_vente' => $lieu_vente,
            'caisses_banque' => $caisseRep->findCaisseDocumentByLieuByType($lieu_vente, 'banque'), 
            'config' => $configRep->findOneBy([]),
        ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set("isPhpEnabled", true);
        $options->set("isHtml5ParserEnabled", true);

        // Instancier Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier (A4 par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF (stream le PDF au navigateur)
        $dompdf->render();

        // Renvoyer une réponse avec le contenu du PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename='.$facturation->getNumeroFacture().'"prépa-livraison.pdf"',
        ]);
    }

    #[Route('/prepa/commande2/{id}/{lieu_vente}', name: 'app_logescom_vente_facturation_prepa_commande2')]
    public function prepaCommande2(Facturation $facturation, ConfigurationLogicielRepository $configRep, LieuxVentes $lieu_vente, CaisseRepository $caisseRep, ClientRepository $clientRep, CommandeProductRepository $commandeProdRep, ListeStockRepository $listeStockRep)
    {        
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$lieu_vente->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $commandes = $commandeProdRep->findBy(['facturation' => $facturation]);
        $listeStocks = $listeStockRep->findAll();

        $html = $this->renderView('logescom/vente/facturation/prepa_commande2.html.twig', [
            'facturation' => $facturation,
            'commandes' => $commandes,
            'listeStocks' => $listeStocks,
            'client' => $clientRep->findOneBy(['user' => $facturation->getClient()]),
            'logoPath' => $logoBase64,
            'lieu_vente' => $lieu_vente,
            'caisses_banque' => $caisseRep->findCaisseDocumentByLieuByType($lieu_vente, 'banque'), 
            'config' => $configRep->findOneBy([]), 
            // 'qrCode'    => $qrCode,
        ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set("isPhpEnabled", true);
        $options->set("isHtml5ParserEnabled", true);

        // Instancier Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier (A4 par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF (stream le PDF au navigateur)
        $dompdf->render();

        // Renvoyer une réponse avec le contenu du PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename='.$facturation->getNumeroFacture().'"prépa-livraison.pdf"',
        ]);
    }
    
    
    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_vente_facturation_delete')]
    public function delete(Facturation $facturation, LieuxVentes $lieu_vente, LivraisonRepository $livraisonRep, CommandeProductRepository $commandeProdRep, StockRepository $stockRep, MouvementProductRepository $mouvementRep, LiaisonProduitRepository $liaisonRep, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$facturation->getId(), $request->request->get('_token'))) {
            $commandes = $commandeProdRep->findBy(['facturation' => $facturation]);
            $livraisons = $livraisonRep->findBy(['commande' => $commandes]);
            foreach ($livraisons as $livraison) {
                $stock = $stockRep->findOneBy(['stockProduit' => $livraison->getStock(), 'product' => $livraison->getCommande()->getProduct()]);               
                
                $liaison = $liaisonRep->findOneBy(['produit2' => $livraison->getCommande()->getProduct()]);
                
                if ($liaison) {
                    $product_liaison_1 = $liaison->getProduit1();    
                    $stock_liaison_1 = $stockRep->findOneBy(['stockProduit' => $livraison->getStock(), 'product' => $product_liaison_1]);
                    if ($liaison->getType() == 'detail') {
                        // dd($stock->getQuantite() , $livraison->getQuantiteLiv());
                        if (($stock->getQuantite() + $livraison->getQuantiteLiv()) >= $product_liaison_1->getNbrePiece()) {
                            $mouvements = $mouvementRep->findBy(['livraison' => $livraison, 'product' => $livraison->getCommande()->getProduct()]);

                            if(sizeof($mouvements)==1){

                                if (($stock->getQuantite() + $livraison->getQuantiteLiv()) == $product_liaison_1->getNbrePiece()) {// pour remmettre dans le carton
                                    $mouvement_liaison_1 = new MouvementProduct(); // on ajoute 1 au carton
                                    $mouvement_liaison_1->setStockProduct($livraison->getStock())
                                                ->setProduct($product_liaison_1)
                                                ->setQuantite(1)
                                                ->setLieuVente($lieu_vente)
                                                ->setPersonnel($this->getUser())
                                                ->setDateOperation(new \DateTime("now"))
                                                ->setOrigine("liaison produit")
                                                ->setDescription("liaison produit")
                                                ->setClient($livraison->getCommande()->getFacturation()->getClient());
                                    // $livraison->addMouvementProduct($mouvement_liaison_1);
                                    $em->persist($mouvement_liaison_1);


                                    $mouvement_liaison_2 = new MouvementProduct(); // on enleve le nombre de pièce dans le détail
                                    $mouvement_liaison_2->setStockProduct($livraison->getStock())
                                                ->setProduct($livraison->getCommande()->getProduct())
                                                ->setQuantite(-$product_liaison_1->getNbrePiece())
                                                ->setLieuVente($lieu_vente)
                                                ->setPersonnel($this->getUser())
                                                ->setDateOperation(new \DateTime("now"))
                                                ->setOrigine("liaison produit")
                                                ->setDescription("liaison produit")
                                                ->setClient($livraison->getCommande()->getFacturation()->getClient());
                                    // $livraison->addMouvementProduct($mouvement_liaison_2);
                                    $em->persist($mouvement_liaison_2);
                                }
                            }
                            // on augmente le stock du carton
                            $stock_liaison_1->setQuantite($stock_liaison_1->getQuantite() + 1);
                            
                            $em->persist($stock_liaison_1);

                            $stock->setQuantite(($stock->getQuantite()) - $product_liaison_1->getNbrePiece());
                            $em->persist($stock);         
                        }
                    }

                    if ($liaison->getType() == 'paquet') {
                        // dd($stock->getQuantite() , $livraison->getQuantiteLiv());
                        if (($stock->getQuantite() + $livraison->getQuantiteLiv()) >= $product_liaison_1->getNbrePaquet()) {
                            $mouvements = $mouvementRep->findBy(['livraison' => $livraison, 'product' => $livraison->getCommande()->getProduct()]);

                            if(sizeof($mouvements)==1){

                                if (($stock->getQuantite() + $livraison->getQuantiteLiv()) == $product_liaison_1->getNbrePaquet()) {// pour remmettre dans le carton
                                    $mouvement_liaison_1 = new MouvementProduct(); // on ajoute 1 au carton
                                    $mouvement_liaison_1->setStockProduct($livraison->getStock())
                                                ->setProduct($product_liaison_1)
                                                ->setQuantite(1)
                                                ->setLieuVente($lieu_vente)
                                                ->setPersonnel($this->getUser())
                                                ->setDateOperation(new \DateTime("now"))
                                                ->setOrigine("liaison produit")
                                                ->setDescription("liaison produit")
                                                ->setClient($livraison->getCommande()->getFacturation()->getClient());
                                    // $livraison->addMouvementProduct($mouvement_liaison_1);
                                    $em->persist($mouvement_liaison_1);


                                    $mouvement_liaison_2 = new MouvementProduct(); // on enleve le nombre de pièce dans le détail
                                    $mouvement_liaison_2->setStockProduct($livraison->getStock())
                                                ->setProduct($livraison->getCommande()->getProduct())
                                                ->setQuantite(-$product_liaison_1->getNbrePaquet())
                                                ->setLieuVente($lieu_vente)
                                                ->setPersonnel($this->getUser())
                                                ->setDateOperation(new \DateTime("now"))
                                                ->setOrigine("liaison produit")
                                                ->setDescription("liaison produit")
                                                ->setClient($livraison->getCommande()->getFacturation()->getClient());
                                    // $livraison->addMouvementProduct($mouvement_liaison_2);
                                    $em->persist($mouvement_liaison_2);
                                }
                            }
                            // on augmente le stock du carton
                            $stock_liaison_1->setQuantite($stock_liaison_1->getQuantite() + 1);
                            
                            $em->persist($stock_liaison_1);

                            $stock->setQuantite(($stock->getQuantite()) - $product_liaison_1->getNbrePaquet());
                            $em->persist($stock);         
                        }
                    }
                }

                // remettre les quantités livrées dans le stock
                $stock->setQuantite($stock->getQuantite() + $livraison->getQuantiteLiv());
                $em->persist($stock);

            }
            $em->remove($facturation);
            $suppressionFacture = new SuppressionFacture();
            $suppressionFacture->setNumeroFacture($facturation->getNumeroFacture())
                    ->setTotalFacture($facturation->getTotalFacture())
                    ->setMontantPaye($facturation->getMontantPaye())
                    ->setMontantRemise($facturation->getMontantRemise())
                    ->setFraisSup($facturation->getFraisSup())
                    ->setClient($facturation->getClient())
                    ->setNomClientCash($facturation->getNomClientCash())
                    ->setCaisse($facturation->getCaisse())
                    ->setEtat($facturation->getEtat())
                    ->setEtatLivraison($facturation->getEtatLivraison())
                    ->setCommentaire($facturation->getCommentaire())
                    ->setModePaie($facturation->getModePaie())
                    ->setLieuVente($lieu_vente)
                    ->setSaisiePar($this->getUser())
                    ->setDateFacturation($facturation->getDateFacturation())
                    ->setDateSaisie(new \DateTime("now"));
            $em->persist($suppressionFacture); 
            $em->flush();
            $this->addFlash("success", "facture supprimée avec succès :)");
        }
        return $this->redirectToRoute("app_logescom_vente_facturation_index", ['lieu_vente' => $lieu_vente->getId()]);
    }
}
