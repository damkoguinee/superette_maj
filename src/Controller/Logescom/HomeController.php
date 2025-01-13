<?php

namespace App\Controller\Logescom;

use App\Entity\Stock;
use App\Entity\Products;
use App\Entity\Entreprise;
use App\Entity\LieuxVentes;
use App\Entity\AnomalieProduit;
use App\Entity\MouvementProduct;
use App\Repository\StockRepository;
use App\Repository\ClientRepository;
use App\Repository\DeviseRepository;
use App\Repository\RegionRepository;
use App\Repository\LicenceRepository;
use App\Repository\ProductsRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ConfigurationLogicielRepository;
use App\Repository\MouvementCollaborateurRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/logescom/home', name: 'app_logescom_home')]
    public function index(EntrepriseRepository $entrepriseRep, LicenceRepository $licenceRep, LieuxVentesRepository $lieuxVentesRep, MouvementCollaborateurRepository $mouvementColabRep, ClientRepository $clientRep, DeviseRepository $deviseRep, RegionRepository $regionRep, Request $request, ConfigurationLogicielRepository $configurationLogicielRep): Response
    {
        $user = $this->getUser();
        if (!$user) {
            // Utilisateur non connecté, redirection vers la page de connexion
            return new RedirectResponse($this->generateUrl('app_login'));
        }
        if ($this->isGranted('ROLE_ADMIN') or $this->isGranted('ROLE_DEVELOPPEUR') || $this->isGranted('ROLE_RESPONSABLE')) { 
            $lieux_ventes = $lieuxVentesRep->findAll();
        } else {
            $lieux_ventes = $lieuxVentesRep->findBy(['id' => $user->getLieuVente()->getId()]);
        }

        $licence = $licenceRep->findOneBy([]);
        // Supposons que vous avez un objet Licence avec les propriétés dateFin (DateTime)
        $dateActuelle = new \DateTime();
        $alerteExpiration = false;
        $licenceExpiree = false;

        // Vérifier si la licence est déjà expirée
        if ($licence->getTypeLicence() == 'illimité') {
            $licenceExpiree = false;
        }elseif ($licence->getDateFin() < $dateActuelle) {
            $licenceExpiree = true;
        } else {
            // Calculer la différence entre la date actuelle et la date d'expiration
            $interval = $licence->getDateFin()->diff($dateActuelle);

            // Vérifier si la licence expire dans un mois ou moins
            if ($interval->invert == 1 && $interval->days <= 30) {
                // La licence expire dans moins d'un mois
                $alerteExpiration = true;
            }
        }

        if ($request->get("id_client_search")){
            $search = $request->get("id_client_search");
        }else{
            $search = "";
        }

        $type1 = $request->get('type1') ? $request->get('type1') : 'client';
        $type2 = $request->get('type2') ? $request->get('type2') : 'client-fournisseur';

        if ($request->get("limit")){
            $limit = $request->get("limit");
        }else{
            $limit = 45;
        } 
        $regions = $regionRep->findBy([], ['nom' => 'ASC']);
        $devises = $deviseRep->findBy(['id' => 1]);
        
        $clients = $clientRep->listeDesClientsGeneralParType($type1, $type2);  
        
        $comptesInactifs = [];
        foreach ($clients as $client) {
            $soldes = $mouvementColabRep->comptesInactif($client->getUser(), $limit, $devises);
            if ($soldes) {
                $comptesInactifs[] = [
                    'collaborateur' => $client->getUser(),
                    'soldes' => $mouvementColabRep->comptesInactif($client->getUser(), $limit, $devises),
                    'derniereOp' => $mouvementColabRep->findOneBy(['collaborateur' => $client->getUser()], ['id' => 'DESC'])
                ];
            }
        }

        // dd($comptesInactifs);

        if ($request->get("region")){
            $region_find = $regionRep->find($request->get("region"));
        }else{
            $region_find = array();
        }

        
        // dd($comptesInactifs);
        return $this->render('logescom/home/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieux_ventes' => $lieux_ventes,
            'config' => $configurationLogicielRep->findOneBy([]),
            'licence' => $licence,
            'alerteExpiration' => $alerteExpiration,
            'licenceExpiree' => $licenceExpiree,
            'search' => $search,
            'comptes' => $comptesInactifs,
            'devises'   => $devises,
            'regions' => $regions,
            'region_find' => $region_find,
            'type1' => $type1,
            'type2' => $type2,
            'limit' => $limit,

        ]);
    }

    #[Route('/lieuvente/{id}', name: 'app_logescom_home-lieuvente', methods: ['GET', 'POST'])]
    public function homeLieuVente(LieuxVentes $lieuxVentes, Request $request, EntrepriseRepository $entrepriseRep, StockRepository $stockRep, EntityManagerInterface $entityManager): Response
    {

        // Récupérer tous les produits du lieu de vente
        $stocks = $stockRep->listeDesProduitsDisponiblesParLieu($lieuxVentes);

        // Récupérer la date actuelle
        $now = new \DateTime();
        $oneWeek = (clone $now)->modify('+1 week');
        $twoWeeks = (clone $now)->modify('+2 weeks');
        $threeWeeks = (clone $now)->modify('+3 weeks');
        $fourWeeks = (clone $now)->modify('+4 weeks');
        $sixWeeks = (clone $now)->modify('+6 weeks');

        // Initialiser les catégories dans un tableau
        $stockData = [
            'perimé' => [],
            'dans 1 semaine' => [],
            'dans 2 semaines' => [],
            'dans 3 semaines' => [],
            'dans 4 semaines' => [],
            'dans 6 semaines' => [],
        ];

        foreach ($stocks as $stock) {
            // Récupérer la date de péremption
            $datePeremption = $stock->getDatePeremption();

            if ($datePeremption != NULL) {
                // Comparer les dates et classer les produits
                if ($datePeremption < $now) {
                    // Ajouter aux produits expirés
                    $stockData['perimé'][] = $stock;
                } elseif ($datePeremption <= $oneWeek) {
                    // Ajouter aux produits expirant dans 1 semaine
                    $stockData['dans 1 semaine'][] = $stock;
                } elseif ($datePeremption <= $twoWeeks) {
                    // Ajouter aux produits expirant dans 2 semaines
                    $stockData['dans 2 semaines'][] = $stock;
                } elseif ($datePeremption <= $threeWeeks) {
                    // Ajouter aux produits expirant dans 3 semaines
                    $stockData['dans 3 semaines'][] = $stock;
                } elseif ($datePeremption <= $fourWeeks) {
                    // Ajouter aux produits expirant dans 4 semaines
                    $stockData['dans 4 semaines'][] = $stock;
                } elseif ($datePeremption <= $sixWeeks) {
                    // Ajouter aux produits expirant dans 6 semaines
                    $stockData['dans 6 semaines'][] = $stock;
                }
            }
        }

        // Debug pour vérifier le contenu
        // dd($stockData);

        // Passer les données à la vue Twig
        return $this->render('logescom/home/choix.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieuxVentes,
            'stockData' => $stockData,
        ]);
    }

    #[Route('/alert/{id}/{alert}', name: 'app_logescom_home_alert', methods: ['GET', 'POST'])]
    public function alertProduct(LieuxVentes $lieuxVentes, $alert, Request $request, EntrepriseRepository $entrepriseRep, StockRepository $stockRep, EntityManagerInterface $entityManager): Response
    {
       // Récupérer tous les produits du lieu de vente
        $stocks = $stockRep->listeDesProduitsDisponiblesParLieu($lieuxVentes);

        // Récupérer la date actuelle
        $now = new \DateTime();
        $oneWeek = (clone $now)->modify('+1 week');
        $twoWeeks = (clone $now)->modify('+2 weeks');
        $threeWeeks = (clone $now)->modify('+3 weeks');
        $fourWeeks = (clone $now)->modify('+4 weeks');
        $sixWeeks = (clone $now)->modify('+6 weeks');

        // Initialiser les catégories dans un tableau
        $stockData = [
            'perimé' => [],
            'dans 1 semaine' => [],
            'dans 2 semaines' => [],
            'dans 3 semaines' => [],
            'dans 4 semaines' => [],
            'dans 6 semaines' => [],
        ];

        foreach ($stocks as $stock) {
            // Récupérer la date de péremption
            $datePeremption = $stock->getDatePeremption();

            if ($datePeremption != NULL) {
                // Comparer les dates et classer les produits
                if ($datePeremption < $now) {
                    // Ajouter aux produits expirés
                    $stockData['perimé'][] = $stock;
                } elseif ($datePeremption <= $oneWeek) {
                    // Ajouter aux produits expirant dans 1 semaine
                    $stockData['dans 1 semaine'][] = $stock;
                } elseif ($datePeremption <= $twoWeeks) {
                    // Ajouter aux produits expirant dans 2 semaines
                    $stockData['dans 2 semaines'][] = $stock;
                } elseif ($datePeremption <= $threeWeeks) {
                    // Ajouter aux produits expirant dans 3 semaines
                    $stockData['dans 3 semaines'][] = $stock;
                } elseif ($datePeremption <= $fourWeeks) {
                    // Ajouter aux produits expirant dans 4 semaines
                    $stockData['dans 4 semaines'][] = $stock;
                } elseif ($datePeremption <= $sixWeeks) {
                    // Ajouter aux produits expirant dans 6 semaines
                    $stockData['dans 6 semaines'][] = $stock;
                }
            }
        }

        // Filtrage selon l'alerte
        $filteredStocks = [];

        foreach ($stocks as $stock) {
            // Récupérer la catégorie du produit
            $categorie = $stock->getProducts()->getCategorie();

            // Récupérer la date de péremption
            $datePeremption = $stock->getDatePeremption();

            if ($datePeremption != NULL) {
                // Filtrer selon la valeur de $alert et regrouper par catégorie
                switch ($alert) {
                    case 'perimé':
                        if ($datePeremption < $now) {
                            $filteredStocks[$categorie->getNameCategorie()][] = $stock;
                        }
                        break;
                    case '1':
                        if ($datePeremption <= $oneWeek && $datePeremption > $now) {
                            $filteredStocks[$categorie->getNameCategorie()][] = $stock;
                        }
                        break;
                    case '2':
                        if ($datePeremption <= $twoWeeks && $datePeremption > $oneWeek) {
                            $filteredStocks[$categorie->getNameCategorie()][] = $stock;
                        }
                        break;
                    case '3':
                        if ($datePeremption <= $threeWeeks && $datePeremption > $twoWeeks) {
                            $filteredStocks[$categorie->getNameCategorie()][] = $stock;
                        }
                        break;
                    case '4':
                        if ($datePeremption <= $fourWeeks && $datePeremption > $threeWeeks) {
                            $filteredStocks[$categorie->getNameCategorie()][] = $stock;
                        }
                        break;
                    case '6':
                        if ($datePeremption <= $sixWeeks && $datePeremption > $fourWeeks) {
                            $filteredStocks[$categorie->getNameCategorie()][] = $stock;
                        }
                        break;
                    default:
                        // Si l'alerte ne correspond à aucun critère, récupérer tous les stocks
                        $filteredStocks[$categorie->getNameCategorie()][] = $stock;
                        break;
                }
            }
        }

        // Trier les stocks par catégorie (les clés du tableau)
        ksort($filteredStocks);

        // Parcourir chaque catégorie pour trier les stocks par date de péremption
        foreach ($filteredStocks as $category => $stocks) {
            usort($stocks, function ($a, $b) {
                return $a->getDatePeremption() <=> $b->getDatePeremption(); // Tri du plus ancien au plus récent
            });
            $filteredStocks[$category] = $stocks; // Remettre les stocks triés dans la catégorie
        }
        // Passer les données filtrées à la vue Twig
        return $this->render('logescom/home/alert_traitement.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieuxVentes,
            'filteredStocks' => $filteredStocks,
            'stockData' => $stockData,
            'alertDate' => $alert,
        ]);
    }

    #[Route('/alert/traitement/{id}/{stock}', name: 'app_logescom_home_alert_traitement', methods: ['GET', 'POST'])]
    public function alertTraitement(LieuxVentes $lieuxVentes, Stock $stock, Request $request, EntityManagerInterface $entityManager): Response
    {
        $qtite_maj = $request->get('qtite');
        $date_maj = $request->get('date');

        if ($date_maj) {
            $stock->setDatePeremption(new \DateTime($date_maj));
            $entityManager->persist($stock);
        }

        if ($qtite_maj) {
            $stock->setQuantite($stock->getQuantite() - $qtite_maj);

            $anomalieProduit = new AnomalieProduit();
            $anomalieProduit->setStock($stock->getStockProduit())
                ->setProduct($stock->getProducts())
                ->setQuantite(-$qtite_maj)
                ->setPrixRevient($stock->getPrixRevient())
                ->setPersonnel($this->getUser())
                ->setDateAnomalie(new \DateTime("now"));

            // on insère dans le mouvement des produits
            $mouvementProduct = new MouvementProduct();
            $mouvementProduct->setStockProduct($stock->getStockProduit())
                ->setProduct($stock->getProducts())
                ->setPersonnel($this->getUser())
                ->setQuantite(-$qtite_maj)
                ->setLieuVente($stock->getStockProduit()->getLieuVente())
                ->setOrigine("stock")
                ->setDescription($anomalieProduit->getComentaire())
                ->setDateOperation(new \DateTime("now"));
            $anomalieProduit->addMouvementProduct($mouvementProduct);

            $entityManager->persist($anomalieProduit);
            $entityManager->persist($mouvementProduct);

        }
        // dd($stock, $anomalieProduit, $mouvementProduct);
        $this->addFlash('success', 'Produit ajusté avec succès :)');
        $entityManager->flush();

        $referer = $request->headers->get('referer');        
        return $this->redirect($referer);
        
    }
    
}
