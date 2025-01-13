<?php

namespace App\Controller\Logescom\Bilan;

use App\Entity\CategorieOperation;
use App\Entity\LieuxVentes;
use App\Entity\ClotureCaisse;
use App\Entity\MouvementCaisse;
use App\Repository\UserRepository;
use App\Repository\CaisseRepository;
use App\Repository\DeviseRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ModePaiementRepository;
use App\Repository\ClotureCaisseRepository;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Repository\ChequeEspeceRepository;
use App\Repository\CommandeProductRepository;
use App\Repository\DecaissementRepository;
use App\Repository\DepensesRepository;
use App\Repository\EchangeDeviseRepository;
use App\Repository\FacturationRepository;
use App\Repository\RecetteRepository;
use App\Repository\TransfertFondRepository;
use App\Repository\VersementRepository;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/bilan')]
class BilanController extends AbstractController
{
    #[Route('/{lieu_vente}', name: 'app_logescom_bilan_index')]
    public function index(LieuxVentes $lieu_vente, Request $request, SessionInterface $session, MouvementCaisseRepository $mouvementRep, UserRepository $userRep, DeviseRepository $deviseRep, CaisseRepository $caisseRep, FacturationRepository $facturationRep, VersementRepository $versementRepository, CommandeProductRepository $commandeProdRep, DepensesRepository $depenseRep, RecetteRepository $recetteRepository, ModePaiementRepository $modePaieRep, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-m-d");
            $date2 = date("Y-m-d");
        }

        if ($request->query->get('id_personnel') or $request->isXmlHttpRequest()) {
            $date1 = $date1;
            $date2 = $date2;
        }else{
            if ($request->get("date1")){
                $date1 = $request->get("date1");
                $date2 = $request->get("date2");
            }else{
                $date1 = date("Y-m-d");
                $date2 = date("Y-m-d");
            }
            $date1 = $date1;
            $date2 = $date2;

            $session->set("session_date1", $date1);
            $session->set("session_date2", $date2);
        }

        if ($request->get("id_personnel")){
            $search = $request->get("id_personnel");
        }else{
            $search = "";
        }

        if ($request->get("search_devise")){
            $search_devise = $deviseRep->find($request->get("search_devise"));
        }else{
            $search_devise = $deviseRep->find(1);
        }

        if ($request->get("search_caisse")){
            $search_caisse = $caisseRep->find($request->get("search_caisse"));
        }else{
            $search_caisse = $caisseRep->findOneBy([]);
        }

        if ($request->isXmlHttpRequest()) {
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
        $caisses = $caisseRep->findCaisseByLieu($lieu_vente);
        $devises = $deviseRep->findAll();
        if ($request->get("id_personnel")){
            $solde_caisses = $mouvementRep->soldeCaisseParPeriodeParVendeurParLieu($search, $date1, $date2, $lieu_vente);
            $solde_types = $mouvementRep->soldeCaisseParPeriodeParTypeParVendeurParLieu($search, $date1, $date2, $lieu_vente);
        }else{
            $solde_caisses = $mouvementRep->soldeCaisseParPeriodeParLieu($date1, $date2, $lieu_vente, $devises, $caisses);
            $solde_caisses_cheque = $mouvementRep->soldeCaisseParModePaieParPeriodeParLieu($date1, $date2, 4, $lieu_vente, $devises, $caisses);

            // Parcourir les soldes des caisses
            foreach ($solde_caisses as &$solde_caisse) {
                // On vérifie si c'est une caisse
                if ($solde_caisse['type_caisse'] === 'caisse') {
                    // Rechercher le solde correspondant dans solde_caisses_cheque
                    foreach ($solde_caisses_cheque as $cheque) {
                        // Vérifier si la caisse et la devise correspondent
                        if ($solde_caisse['id_caisse'] == $cheque['id_caisse'] && $solde_caisse['id_devise'] == $cheque['id_devise']) {
                            // Déduire le montant du chèque du solde de la caisse
                            $solde_caisse['solde'] -= $cheque['solde'];
                        }
                    }
                }
            }

            // Vous pouvez maintenant utiliser $solde_caisses mis à jour avec les déductions de chèques
            $caisses_lieu = [];
            foreach ($solde_caisses as $solde) {
                foreach ($caisses as $caisse) {
                    if ($solde['id_caisse'] == $caisse->getId()) {
                        $caisses_lieu[$caisse->getDesignation()][] = $solde;
                    } 
                }
            }

            // Maintenant, on ajoute 'caisse_cheque' au même niveau uniquement si le type de caisse est 'caisse'
            foreach ($solde_caisses_cheque as $cheque) {
                foreach ($caisses as $caisse) {
                    if ($cheque['id_caisse'] == $caisse->getId() && $cheque['type_caisse'] == 'caisse') {
                        // On ajoute uniquement si le type de caisse est 'caisse'
                        $caisses_lieu[$caisse->getDesignation() . ' chèque'][] = [
                            'solde' => $cheque['solde'],
                            'id_caisse' => $cheque['id_caisse'],
                            'type_caisse' => $cheque['type_caisse'],
                            'designation' => $cheque['designation'],
                            'nomDevise' => $cheque['nomDevise'],
                            'id_devise' => $cheque['id_devise']
                        ];
                    }
                }
            }
            uksort($caisses_lieu, function($a, $b) {
                // Priorité à "caisse espèces" et "caisse espèces cheque"
                $priorites = ['caisse', 'caisse chèque'];
            
                // Vérification si $a ou $b sont dans la liste des priorités
                $indexA = array_search($a, $priorites);
                $indexB = array_search($b, $priorites);
            
                // Si $a est prioritaire et pas $b, $a vient avant
                if ($indexA !== false && $indexB === false) {
                    return -1;
                }
            
                // Si $b est prioritaire et pas $a, $b vient avant
                if ($indexB !== false && $indexA === false) {
                    return 1;
                }
            
                // Si $a et $b sont tous les deux dans la liste des priorités, on respecte leur ordre dans la liste
                if ($indexA !== false && $indexB !== false) {
                    return $indexA - $indexB;
                }
            
                // Si ni $a ni $b ne sont dans la liste des priorités, on applique la logique habituelle pour les caisses avec 'cheque'
                if (strpos($a, 'cheque') === false && strpos($b, 'cheque') !== false && strpos($b, $a) === 0) {
                    return -1;
                }
            
                if (strpos($b, 'cheque') === false && strpos($a, 'cheque') !== false && strpos($a, $b) === 0) {
                    return 1;
                }
            
                // Sinon, tri alphabétique standard
                return strcmp($a, $b);
            });

            if ($request->get("search_caisse")){
                $solde_types = $mouvementRep->soldeCaisseParPeriodeParTypeParLieuParDeviseParCaisse($date1, $date2, $lieu_vente, $search_devise, $search_caisse);

            }else{
                $solde_types = $mouvementRep->soldeCaisseParPeriodeParTypeParLieuParDevise($date1, $date2, $lieu_vente, $search_devise);

            }

        }
        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get('operation')) {
            $operation_search = $request->get("operation");
            if ($operation_search == 'facturation') {
                $operations_search = $facturationRep->findFacturationByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);
            }elseif ($operation_search == 'versement') {
                $operations_search = $versementRepository->findVersementByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);

            }elseif ($operation_search == 'recette') {
                $operations_search = $recetteRepository->findRecetteByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);

            }else{
                $operations_search = array();
            }
        }else{
            $operation_search = array();
            $operations_search = array();

        }
        $compte_operations = $mouvementRep->compteOperationParPeriodeParLieu($date1, $date2, $lieu_vente);

        $solde_caisses_devises = $mouvementRep->soldeCaisseParDeviseParLieu($date1, $date2, $lieu_vente, $devises);

        $modesPaie = $modePaieRep->findAll();
        $facturations_data = $mouvementRep->mouvementCaisseParTypeParPeriodeParLieu('facturation', $date1, $date2, $lieu_vente, $devises, $modesPaie);

        $facturations = [];
        foreach ($facturations_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                    $facturations[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                }
            }

        }

        $versements_data = $mouvementRep->mouvementCaisseParTypeParPeriodeParLieu('versement', $date1, $date2, $lieu_vente, $devises, $modesPaie);

        $versements = [];
        foreach ($versements_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                    $versements[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                }
            }

        }

        $echanges_data = $mouvementRep->mouvementCaisseParTypeParPeriodeParLieu('echange', $date1, $date2, $lieu_vente, $devises, $modesPaie);
        // dd($echanges_data);

        $echanges = [];
        foreach ($echanges_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                    if ($modePaie->getId() == 1) {
                        $echanges[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                    }
                }
            }

        }
        

        $transferts_data = $mouvementRep->mouvementCaisseParTypeParPeriodeParLieuEntree('transfert', $date1, $date2, $lieu_vente, $devises, $modesPaie);
        // dd($transferts_data);

        $transferts = [];
        foreach ($transferts_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                    
                    $transferts[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                    
                }
            }

        }

        $transferts_data = $mouvementRep->mouvementCaisseParTypeParPeriodeParLieuSortie('transfert', $date1, $date2, $lieu_vente, $devises, $modesPaie);
        // dd($transferts_data);

        $transfertsSortie = [];
        foreach ($transferts_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                     
                    $transfertsSortie[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                    
                }
            }

        }

        $recettes_data = $mouvementRep->mouvementCaisseParTypeParPeriodeParLieu('recette', $date1, $date2, $lieu_vente, $devises, $modesPaie);

        $recettes = [];
        foreach ($recettes_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                    if ($value['montantTotal'] > 0) {
                        $recettes[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                    }
                }
            }

        }

        $clotures_data = $mouvementRep->mouvementCaisseParTypeParPeriodeParLieu('cloture', $date1, $date2, $lieu_vente, $devises, $modesPaie);

        $clotures = [];
        foreach ($clotures_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                    if ($value['montantTotal'] > 0) {
                        $clotures[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                    }
                }
            }

        }

        $clotures_manquant = [];
        foreach ($clotures_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                    if ($value['montantTotal'] < 0) {
                        $clotures_manquant[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                    }
                }
            }

        }

        $decaissements_data = $mouvementRep->mouvementCaisseParTypeParPeriodeParLieu('decaissement', $date1, $date2, $lieu_vente, $devises, $modesPaie);

        $decaissements = [];
        foreach ($decaissements_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                    $decaissements[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                }
            }

        }

        $depenses_data = $mouvementRep->mouvementCaisseParTypeParPeriodeParLieu('depenses', $date1, $date2, $lieu_vente, $devises, $modesPaie);

        $depenses = [];
        foreach ($depenses_data as $value) {
            foreach ($modesPaie as $modePaie) {
                if ($value['id_mode_paie'] == $modePaie->getId()) {
                    $depenses[$value['typeMouvement']][$value['typeMouvement']." ".$value['modePaiement']][] = $value;
                }
            }

        }

        $solde_caisses_type = $mouvementRep->soldeCaisseParDeviseParLieuParType('facturation', $date1, $date2, $lieu_vente, $devises);
        $chiffre_affaire = $facturationRep->chiffreAffaireParPeriodeParLieu($lieu_vente, $date1, $date2);
        $facturationPayees = $facturationRep->facturationsPayeesParPeriodeParLieu($lieu_vente, $date1, $date2);
        $totauxEncaissements = $mouvementRep->totauxEncaissementsParPeriodeParLieu($date1, $date2, $lieu_vente, $devises);

        $totalEntrees = $mouvementRep->totalEntreesParPeriodeParLieu($date1, $date2, $lieu_vente, $devises);
        $totauxDecaissements = $mouvementRep->totauxDecaissementsParPeriodeParLieu($date1, $date2, $lieu_vente, $devises);
        $nombreDeVentes = $facturationRep->nombreDeVentesParPeriodeParLieu($date1, $date2, $lieu_vente);
        $totalDepenses = $depenseRep->totalDepensesParPeriodeParLieu($lieu_vente, $date1, $date2);
        $totalDepensesParDevise = $depenseRep->totalDepensesParPeriodeParLieuParDevise($date1, $date2, $lieu_vente, $deviseRep->find(1));
        $beneficeVentes = $commandeProdRep->beneficeDesVentesParPeriodeParLieu($date1, $date2, $lieu_vente);
      

        return $this->render('logescom/bilan/bilan/index.html.twig', [
            'solde_caisses' => $caisses_lieu,
            'solde_caisses_devises' => $solde_caisses_devises,
            'solde_types' => $solde_types,
            'facturations' => $facturations,
            'solde_caisses_type' => $solde_caisses_type,
            'chiffre_affaire' => $chiffre_affaire,
            'facturations_payees' => $facturationPayees,
            'versements' => $versements,
            'clotures' => $clotures,
            'clotures_manquant' => $clotures_manquant,
            'echanges' => $echanges,
            'transferts' => $transferts,
            'transfertsSortie' => $transfertsSortie,
            'recettes' => $recettes,
            'decaissements' => $decaissements,
            'totauxEncaissements' => $totauxEncaissements,
            'totalEntrees' => $totalEntrees,
            'totauxDecaissements' => $totauxDecaissements,
            'nombre_ventes' => $nombreDeVentes,
            'total_depenses' => $totalDepenses,
            'total_depenses_devise' => $totalDepensesParDevise,
            'benefice_ventes' => $beneficeVentes,
            'depenses' => $depenses,
            'compte_operations' => $compte_operations,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente'   => $lieu_vente,
            'liste_caisse' => $caisseRep->findCaisseByLieu($lieu_vente),
            'search' => $search,
            'search_devise' => $search_devise,
            'search_caisse' => $search_caisse,
            'devises' => $devises,
            'date1' => $date1,
            'date2' => $date2,
            'operation_search' => $operation_search ? $operation_search : array(), 
            'op_facturations' => $operations_search,
        ]);
    }

    #[Route('/etat/{lieu_vente}', name: 'app_logescom_bilan_etat_caisse')]
    public function etatCaisse(LieuxVentes $lieu_vente, CaisseRepository $caisseRep, DeviseRepository $deviseRep, MouvementCaisseRepository $mouvementRep, Request $request, UserRepository $userRep, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("2000-m-d");
            $date2 = date("Y-m-d");
        }

        $caisses = $caisseRep->findCaisseByLieu($lieu_vente);
        $devises = $deviseRep->findAll();
        $solde_caisses = $mouvementRep->soldeCaisseParPeriodeParLieu($date1, $date2, $lieu_vente, $devises, $caisses);
        
        $solde_caisses_cheque = $mouvementRep->soldeCaisseParModePaieParPeriodeParLieu($date1, $date2, 4, $lieu_vente, $devises, $caisses);

        // dd($solde_caisses_cheque);

        $caisses_lieu = [];
        // Parcourir les soldes des caisses
        foreach ($solde_caisses as &$solde_caisse) {
            // On vérifie si c'est une caisse
            if ($solde_caisse['type_caisse'] === 'caisse') {
                // Rechercher le solde correspondant dans solde_caisses_cheque
                foreach ($solde_caisses_cheque as $cheque) {
                    // Vérifier si la caisse et la devise correspondent
                    if ($solde_caisse['id_caisse'] == $cheque['id_caisse'] && $solde_caisse['id_devise'] == $cheque['id_devise']) {
                        // Déduire le montant du chèque du solde de la caisse
                        $solde_caisse['solde'] -= $cheque['solde'];
                    }
                }
            }
        }

        // Vous pouvez maintenant utiliser $solde_caisses mis à jour avec les déductions de chèques
        $caisses_lieu = [];
        foreach ($solde_caisses as $solde) {
            foreach ($caisses as $caisse) {
                if ($solde['id_caisse'] == $caisse->getId()) {
                    $caisses_lieu[$caisse->getDesignation()][] = $solde;
                } 
            }
        }

        // Maintenant, on ajoute 'caisse_cheque' au même niveau uniquement si le type de caisse est 'caisse'
        foreach ($solde_caisses_cheque as $cheque) {
            foreach ($caisses as $caisse) {
                if ($cheque['id_caisse'] == $caisse->getId() && $cheque['type_caisse'] == 'caisse') {
                    // On ajoute uniquement si le type de caisse est 'caisse'
                    $caisses_lieu[$caisse->getDesignation() . ' chèque'][] = [
                        'solde' => $cheque['solde'],
                        'id_caisse' => $cheque['id_caisse'],
                        'type_caisse' => $cheque['type_caisse'],
                        'designation' => $cheque['designation'],
                        'nomDevise' => $cheque['nomDevise'],
                        'id_devise' => $cheque['id_devise']
                    ];
                }
            }
        }
        uksort($caisses_lieu, function($a, $b) {
            // Priorité à "caisse espèces" et "caisse espèces cheque"
            $priorites = ['caisse', 'caisse chèque'];
        
            // Vérification si $a ou $b sont dans la liste des priorités
            $indexA = array_search($a, $priorites);
            $indexB = array_search($b, $priorites);
        
            // Si $a est prioritaire et pas $b, $a vient avant
            if ($indexA !== false && $indexB === false) {
                return -1;
            }
        
            // Si $b est prioritaire et pas $a, $b vient avant
            if ($indexB !== false && $indexA === false) {
                return 1;
            }
        
            // Si $a et $b sont tous les deux dans la liste des priorités, on respecte leur ordre dans la liste
            if ($indexA !== false && $indexB !== false) {
                return $indexA - $indexB;
            }
        
            // Si ni $a ni $b ne sont dans la liste des priorités, on applique la logique habituelle pour les caisses avec 'cheque'
            if (strpos($a, 'cheque') === false && strpos($b, 'cheque') !== false && strpos($b, $a) === 0) {
                return -1;
            }
        
            if (strpos($b, 'cheque') === false && strpos($a, 'cheque') !== false && strpos($a, $b) === 0) {
                return 1;
            }
        
            // Sinon, tri alphabétique standard
            return strcmp($a, $b);
        });
        
        // $solde_types = $mouvementRep->soldeCaisseParTypeParLieuParDevise($lieu_vente, $search_devise);

        $solde_caisses_devises = $mouvementRep->soldeCaisseGeneralParDeviseParLieu($lieu_vente, $devises);
        
        return $this->render('logescom/bilan/bilan/etat_caisse.html.twig', [
            'solde_caisses' => $caisses_lieu,
            'solde_caisses_devises' => $solde_caisses_devises,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente'   => $lieu_vente,
            'devises' => $devises,

        ]);
    }

    #[Route('/detail/operation/{lieu_vente}', name: 'app_logescom_bilan_detail_operation')]
    public function detailOperation(LieuxVentes $lieu_vente, Request $request, MouvementCaisseRepository $mouvementRep, DeviseRepository $deviseRep, FacturationRepository $facturationRep, VersementRepository $versementRepository, CommandeProductRepository $commandeProdRep, DepensesRepository $depenseRep, RecetteRepository $recetteRepository, ClotureCaisseRepository $clotureRep, TransfertFondRepository $transfertFondRep, ModePaiementRepository $modePaieRep, DecaissementRepository $decaissementRep, ChequeEspeceRepository $chequeEspeceRep, EchangeDeviseRepository $EchangeDeviseRepository, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-m-d");
            $date2 = date("Y-m-d");
        }
        $devises = $deviseRep->findAll();
        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get('operation')) {
            $operation_search = $request->get("operation");
            if ($operation_search == 'facturation') {
                $operations_search = $facturationRep->findFacturationByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);
            }elseif ($operation_search == 'versement') {

                $operations_search = $versementRepository->findVersementByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);

            }elseif ($operation_search == 'recette') {
                $operations_search = $recetteRepository->findRecetteByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);

            }elseif ($operation_search == 'decaissement') {
                $operations_search = $decaissementRep->findDecaissementByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);
            }elseif ($operation_search == 'depenses') {
                $operations_search = $depenseRep->findDepensesByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);
            }elseif ($operation_search == 'transfert') {
                $operations_search = $transfertFondRep->findTransfertByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);

            }elseif ($operation_search == 'cloture') {
                $operations_search = $clotureRep->listeDesCloturesParPeriodeParLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);
            }elseif ($operation_search == 'echange') {
                $operations_search = $EchangeDeviseRepository->findTransfertByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);

            }elseif ($operation_search == 'cheques-especes') {
                $operations_search = $chequeEspeceRep->findVersementByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 10000);

            }else{
                $operations_search = array();
            }

            $cumul_operations = $mouvementRep->soldeCaisseParDeviseParLieuParType($operation_search, $date1, $date2, $lieu_vente, $devises);

        }else{
            $operation_search = array();
            $operations_search = array();
            $cumul_operations = array();

        }
        $compte_operations = $mouvementRep->compteOperationParPeriodeParLieu($date1, $date2, $lieu_vente);

        $solde_caisses_devises = $mouvementRep->soldeCaisseParDeviseParLieu($date1, $date2, $lieu_vente, $devises);
        return $this->render('logescom/bilan/bilan/detail_operation.html.twig', [ 
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente'   => $lieu_vente,           
            'solde_caisses_devises' => $solde_caisses_devises,
            'date1' => $date1,
            'date2' => $date2,
            'operation_search' => $operation_search ? $operation_search : array(), 
            'op_facturations' => $operations_search,
            'compte_operations' => $compte_operations,
            'cumul_operations' => $cumul_operations,
        ]);
    }

    #[Route('/cloture/{lieu_vente}', name: 'app_logescom_bilan_cloture')]
    public function cloture(LieuxVentes $lieu_vente, Request $request, SessionInterface $session, MouvementCaisseRepository $mouvementRep, UserRepository $userRep, DeviseRepository $deviseRep, CaisseRepository $caisseRep, CategorieOperationRepository $catetgorieOpRep, CompteOperationRepository $compteOpRep, ModePaiementRepository $modePaieRep, ClotureCaisseRepository $clotureRep, EntrepriseRepository $entrepriseRep, EntityManagerInterface $em): Response
    {
        $journee = new DateTime(date("Y-m-d"));
        if ($request->get('montant_reel')) {
            $montant_reel = floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montant_reel')));
            $montant_theo = floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montant_theo')));
            $difference = $montant_theo - $montant_reel;
            $caisse = $caisseRep->find($request->get('id_caisse'));
            $devise = $deviseRep->find($request->get('id_devise'));
            $clotureCaisse = new ClotureCaisse();
            $clotureCaisse->setMontantTheo($montant_theo)
                    ->setMontantReel($montant_reel)
                    ->setDifference($difference)
                    ->setJournee(new \DateTime("now"))
                    ->setDevise($devise)
                    ->setCaisse($caisse)
                    ->setLieuVente($lieu_vente)
                    ->setSaisiePar($this->getUser())
                    ->setDateSaisie(new \DateTime("now"));
            $mouvementCaisse = new MouvementCaisse();

            $categorie_op = $catetgorieOpRep->find(7);
            $compte_op = $compteOpRep->find(7);
            $mouvementCaisse->setCategorieOperation($categorie_op)
                    ->setCompteOperation($compte_op)
                    ->setTypeMouvement("cloture")
                    ->setMontant(- $difference)
                    ->setModePaie($modePaieRep->find(1))
                    ->setSaisiePar($this->getUser())
                    ->setDevise($devise)
                    ->setCaisse($caisse)
                    ->setEtatOperation('traite')
                    ->setLieuVente($lieu_vente)
                    ->setDateOperation(new \DateTime("now"))
                    ->setDateSaisie(new \DateTime("now"));
            $clotureCaisse->addMouvementCaiss($mouvementCaisse);
            $em->persist($clotureCaisse);
            $em->flush();
            $this->addFlash("success", "Caisse clôturée avec succés :) ");
            return new RedirectResponse($this->generateUrl('app_logescom_bilan_cloture', ['lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search")]));
        }

        $caisses = $caisseRep->findCaisseByLieuByType($lieu_vente, 'caisse');
        $devises = $deviseRep->findAll();
        $solde_caisses_especes = $mouvementRep->soldeCaisseGeneralChequesParDeviseParLieuParModePaie($lieu_vente, $devises, $caisses, 'espèces');
        $caisses_especes_lieu = [];
        foreach ($solde_caisses_especes as $solde) {
            // dd($solde);
            foreach ($caisses as $caisse) {
                if ($solde['id_caisse'] == $caisse->getId()) {
                    $caisses_especes_lieu[$caisse->getDesignation()][] = $solde;
                } 
            }
        }

        // dd($caisses_especes_lieu);

        $solde_caisses_cheques = $mouvementRep->soldeCaisseGeneralChequesParDeviseParLieuParModePaie($lieu_vente, $devises, $caisses, 'chèque');
        $caisses_cheques_lieu = [];
        foreach ($solde_caisses_cheques as $solde) {
            foreach ($caisses as $caisse) {
                if ($solde['id_caisse'] == $caisse->getId()) {
                    $caisses_cheques_lieu[$caisse->getDesignation()][] = $solde;
                } 
            }
        }

        $banques = $caisseRep->findCaisseByLieuByType($lieu_vente, 'banque');
        $solde_banques = $mouvementRep->soldeBanquesGeneralParDeviseParLieu($lieu_vente, $devises, $banques);
        $banques_lieu = [];
        foreach ($solde_banques as $solde) {
            foreach ($banques as $banque) {
                if ($solde['id_caisse'] == $banque->getId()) {
                    $banques_lieu[$banque->getDesignation()][] = $solde;
                } 
            }
        }


        return $this->render('logescom/bilan/bilan/cloture.html.twig', [
            'solde_caisses_especes' => $caisses_especes_lieu,
            'solde_caisses_cheques' => $caisses_cheques_lieu,
            'solde_banques' => $banques_lieu,
            'liste_clotures' => $clotureRep->findBy(['lieuVente' => $lieu_vente]),
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente'   => $lieu_vente,
            'liste_caisse' => $caisseRep->findCaisseByLieu($lieu_vente),
            'devises' => $devises,
        ]);
    }
}
