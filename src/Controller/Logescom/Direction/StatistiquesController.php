<?php

namespace App\Controller\Logescom\Direction;

use App\Repository\EntrepriseRepository;
use App\Repository\FacturationRepository;
use App\Repository\LieuxVentesRepository;
use App\Repository\CommandeProductRepository;
use App\Repository\ProductsRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/direction/statistiques')]
class StatistiquesController extends AbstractController
{
    #[Route('/', name: 'app_logescom_direction_statistiques')]
    public function index(CommandeProductRepository $commandProductRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep, Request $request): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        if ($request->get('lieu') and $request->get('lieu') != 'general') {
            $lieu = $lieuVenteRep->find($request->get('lieu'));
            $top_vente_produits = $commandProductRep->topVenteProduitGeneralParPeriodeParLieu($date1, $date2, $lieu);
            $top_benefice_produits = $commandProductRep->topVenteBeneficeProduitGeneralParPeriodeParLieu($date1, $date2, $lieu);

        }else{
            
            $top_vente_produits = $commandProductRep->topVenteProduitGeneralParPeriode($date1, $date2);
            $top_benefice_produits = $commandProductRep->topVenteBeneficeProduitGeneralParPeriode($date1, $date2);
            
        }
        return $this->render('logescom/direction/statistiques/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),
            'date1' => $date1,
            'date2' => $date2,
            'top_vente_produits' => $top_vente_produits,
            'top_benefice_produits' => $top_benefice_produits,
            'lieux' => $lieuVenteRep->findAll(),
        ]);
    }

    #[Route('/client', name: 'app_logescom_direction_statistiques_client')]
    public function topClient(CommandeProductRepository $commandProductRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep, Request $request): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        if ($request->get('lieu') and $request->get('lieu') != 'general') {
            $lieu = $lieuVenteRep->find($request->get('lieu'));
            $top_vente_produits = $commandProductRep->topVenteProduitGeneralParPeriodeParLieuGroupeParClient($date1, $date2, $lieu);
            $top_benefice_produits = $commandProductRep->topVenteBeneficeProduitGeneralParPeriodeParLieuGroupeParClient($date1, $date2, $lieu);

        }else{            
            $top_vente_produits = $commandProductRep->topVenteProduitGeneralParPeriodeGroupeParClient($date1, $date2);
            $top_benefice_produits = $commandProductRep->topVenteBeneficeProduitGeneralParPeriodeGroupeParClient($date1, $date2);
            
        }
        
        return $this->render('logescom/direction/statistiques/top_client.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),
            'date1' => $date1,
            'date2' => $date2,
            'top_vente_produits' => $top_vente_produits,
            'top_benefice_produits' => $top_benefice_produits,
            'lieux' => $lieuVenteRep->findAll(),
        ]);
    }

    #[Route('/ventes/mois', name: 'app_logescom_direction_statistiques_ventes_mois')]
    public function statVentesParMois(FacturationRepository $facturationRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep, Request $request): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        if ($request->get('lieu') and $request->get('lieu') != 'general') {
            $lieu = $lieuVenteRep->find($request->get('lieu'));
            $datasVentesParMois = $facturationRep->nombreDeVentesParPeriodeParLieuGroupeParMois($date1, $date2, $lieu); 
        }else{            
            $datasVentesParMois = $facturationRep->nombreDeVentesParPeriodeGroupeParMois($date1, $date2);            
        }
        $nbreVentesParMois = [];
        $datesMois = [];
        foreach ($datasVentesParMois as $data) {
            $nbreVentesParMois[] = $data['nbre'];
            $datesMois[] = $data['mois'];
        }

        if ($request->get('lieu') and $request->get('lieu') != 'general') {
            $lieu = $lieuVenteRep->find($request->get('lieu'));
            $datasVentesParAnnees = $facturationRep->nombreDeVentesParPeriodeParLieuGroupeParAnnees($date1, $date2, $lieu); 
        }else{            
            $datasVentesParAnnees = $facturationRep->nombreDeVentesParPeriodeGroupeParAnnees($date1, $date2);            
        }
        $nbreVentesParAnnees = [];
        $datesAnnees = [];
        foreach ($datasVentesParAnnees as $data) {
            $nbreVentesParAnnees[] = $data['nbre'];
            $datesAnnees[] = $data['annees'];
        }

        if ($request->get('lieu') and $request->get('lieu') != 'general') {
            $lieu = $lieuVenteRep->find($request->get('lieu'));
            $datasVentesParAnnees = $facturationRep->chiffreAffairesParPeriodeParLieuGroupeParAnnees($date1, $date2, $lieu); 
        }else{            
            $datasVentesParAnnees = $facturationRep->chiffreAffairesParPeriodeGroupeParAnnees($date1, $date2);            
        }
        $chiffreAffaireParAnnees = [];
        $datesChiffreAffaireAnnees = [];
        foreach ($datasVentesParAnnees as $data) {
            $chiffreAffaireParAnnees[] = $data['nbre'];
            $datesChiffreAffaireAnnees[] = $data['annees'];
        }

        if ($request->get('lieu') and $request->get('lieu') != 'general') {
            $lieu = $lieuVenteRep->find($request->get('lieu'));
            $datasVentesParMois = $facturationRep->chiffreAffairesParLieuGroupeParMois($date1, $date2, $lieu); 
        }else{            
            $datasVentesParMois = $facturationRep->chiffreAffairesGroupeParMois($date1, $date2);            
        }
        $chiffreAffaireParMois = [];
        $datesChiffreAffaireMois = [];
        foreach ($datasVentesParMois as $data) {
            $chiffreAffaireParMois[] = $data['nbre'];
            $datesChiffreAffaireMois[] = $data['mois'];
        }
        return $this->render('logescom/direction/statistiques/stat_vente_mois.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),

            'nbre_vente_annees' => json_encode($nbreVentesParAnnees),
            'dates_annees' => json_encode($datesAnnees),

            'chiffre_affaire_annees' => json_encode($chiffreAffaireParAnnees),
            'dates_chiffre_affaire_annees' => json_encode($datesChiffreAffaireAnnees),

            'chiffre_affaire_mois' => json_encode($chiffreAffaireParMois),
            'dates_chiffre_affaire_mois' => json_encode($datesChiffreAffaireMois),

            'nbre_vente_mois' => json_encode($nbreVentesParMois),
            'dates_mois' => json_encode($datesMois),

            'date1' => $date1,
            'date2' => $date2,
            'lieux' => $lieuVenteRep->findAll(),
        ]);
    }


    #[Route('/ventes/client', name: 'app_logescom_direction_statistiques_ventes_client')]
    public function statVentesClient(FacturationRepository $facturationRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep, CommandeProductRepository $commandProductRep, UserRepository $userRep, Request $request): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        if ($request->get("id_client_search")){
            $search = $request->get("id_client_search");
        }else{
            $search = "";
        }
        
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $clients = $userRep->findAllClientSearch($search);    
            $response = [];
            foreach ($clients as $client) {
                $response[] = [
                    'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                    'id' => $client->getId()
                ]; 
            }
            return new JsonResponse($response);
        }

        if ($request->get('id_client_search') ) {
            $datasVentesParMois = $facturationRep->nombreDeVentesParPeriodeParClientGroupeParMois($date1, $date2, $search);

            $nbreVentesParMois = [];
            $datesMois = [];
            foreach ($datasVentesParMois as $data) {
                $nbreVentesParMois[] = $data['nbre'];
                $datesMois[] = $data['mois'];
            }
            $datasVentesParAnnees = $facturationRep->nombreDeVentesParPeriodeParClientGroupeParAnnees($date1, $date2, $search); 

            $nbreVentesParAnnees = [];
            $datesAnnees = [];
            foreach ($datasVentesParAnnees as $data) {
                $nbreVentesParAnnees[] = $data['nbre'];
                $datesAnnees[] = $data['annees'];
            }
            $datasVentesParAnnees = $facturationRep->chiffreAffairesParPeriodeParClientGroupeParAnnees($date1, $date2, $search); 
        
            $chiffreAffaireParAnnees = [];
            $datesChiffreAffaireAnnees = [];
            foreach ($datasVentesParAnnees as $data) {
                $chiffreAffaireParAnnees[] = $data['nbre'];
                $datesChiffreAffaireAnnees[] = $data['annees'];
            }

        
            $datasVentesParMois = $facturationRep->chiffreAffairesParClientGroupeParMois($date1, $date2, $search); 
        
            $chiffreAffaireParMois = [];
            $datesChiffreAffaireMois = [];
            foreach ($datasVentesParMois as $data) {
                $chiffreAffaireParMois[] = $data['nbre'];
                $datesChiffreAffaireMois[] = $data['mois'];
            }

            $top_vente_produits = $commandProductRep->topVenteProduitGeneralParPeriodeParClient($date1, $date2, $search);

            $top_benefice_produits = $commandProductRep->topVenteBeneficeProduitGeneralParPeriodeParClient($date1, $date2, $search);
        }else{
            $nbreVentesParAnnees = [];
            $datesAnnees = [];
            $chiffreAffaireParAnnees =[];
            $datesChiffreAffaireAnnees = [];
            $chiffreAffaireParMois = [];
            $datesChiffreAffaireMois = [];
            $nbreVentesParMois = [];
            $datesMois = [];
            $top_vente_produits = [];
            $top_benefice_produits = [];
        }
        return $this->render('logescom/direction/statistiques/stat_client.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),

            'nbre_vente_annees' => json_encode($nbreVentesParAnnees),
            'dates_annees' => json_encode($datesAnnees),

            'chiffre_affaire_annees' => json_encode($chiffreAffaireParAnnees),
            'dates_chiffre_affaire_annees' => json_encode($datesChiffreAffaireAnnees),

            'chiffre_affaire_mois' => json_encode($chiffreAffaireParMois),
            'dates_chiffre_affaire_mois' => json_encode($datesChiffreAffaireMois),

            'nbre_vente_mois' => json_encode($nbreVentesParMois),
            'dates_mois' => json_encode($datesMois),

            'date1' => $date1,
            'date2' => $date2,

            'lieux' => $lieuVenteRep->findAll(),

            'search' => $userRep->find($search),
            'top_benefice_produits' => $top_benefice_produits,
            'top_vente_produits' => $top_vente_produits,
        ]);
    }

    #[Route('/ventes/produit', name: 'app_logescom_direction_statistiques_ventes_produit')]
    public function statVentesProduit(CommandeProductRepository $commandProductRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep, UserRepository $userRep, ProductsRepository $productsRep, Request $request): Response
    {
        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        if ($request->get("id_product_search")){
            $search = $request->get("id_product_search");
        }else{
            $search = "";
        }
        
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search_product');
            $products = $productsRep->findProductsSearch($search);    
            $response = [];
            foreach ($products as $product) {
                $response[] = [
                    'nom' => $product->getDesignation(),
                    'id' => $product->getId()
                ]; // Mettez à jour avec le nom réel de votre propriété
            }
            return new JsonResponse($response);
        }

        if ($request->get('id_product_search') ) {
            $datasVentesParMois = $commandProductRep->nombreDeVentesParPeriodeParProduitGroupeParMois($date1, $date2, $search);

            $nbreVentesParMois = [];
            $datesMois = [];
            foreach ($datasVentesParMois as $data) {
                $nbreVentesParMois[] = $data['nbre'];
                $datesMois[] = $data['mois'];
            }
            $datasVentesParAnnees = $commandProductRep->nombreDeVentesParPeriodeParProduitGroupeParAnnees($date1, $date2, $search); 

            $nbreVentesParAnnees = [];
            $datesAnnees = [];
            foreach ($datasVentesParAnnees as $data) {
                $nbreVentesParAnnees[] = $data['nbre'];
                $datesAnnees[] = $data['annees'];
            }
            $datasVentesParAnnees = $commandProductRep->chiffreAffairesParPeriodeParProduitGroupeParAnnees($date1, $date2, $search); 
        
            $chiffreAffaireParAnnees = [];
            $datesChiffreAffaireAnnees = [];
            foreach ($datasVentesParAnnees as $data) {
                $chiffreAffaireParAnnees[] = $data['nbre'];
                $datesChiffreAffaireAnnees[] = $data['annees'];
            }

        
            $datasVentesParMois = $commandProductRep->chiffreAffairesParProduitGroupeParMois($date1, $date2, $search); 
        
            $chiffreAffaireParMois = [];
            $datesChiffreAffaireMois = [];
            foreach ($datasVentesParMois as $data) {
                $chiffreAffaireParMois[] = $data['nbre'];
                $datesChiffreAffaireMois[] = $data['mois'];
            }
        }else{
            $nbreVentesParAnnees = [];
            $datesAnnees = [];
            $chiffreAffaireParAnnees =[];
            $datesChiffreAffaireAnnees = [];
            $chiffreAffaireParMois = [];
            $datesChiffreAffaireMois = [];
            $nbreVentesParMois = [];
            $datesMois = [];


        }
        return $this->render('logescom/direction/statistiques/stat_produit.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $this->getUser()->getLieuVente(),

            'nbre_vente_annees' => json_encode($nbreVentesParAnnees),
            'dates_annees' => json_encode($datesAnnees),

            'chiffre_affaire_annees' => json_encode($chiffreAffaireParAnnees),
            'dates_chiffre_affaire_annees' => json_encode($datesChiffreAffaireAnnees),

            'chiffre_affaire_mois' => json_encode($chiffreAffaireParMois),
            'dates_chiffre_affaire_mois' => json_encode($datesChiffreAffaireMois),

            'nbre_vente_mois' => json_encode($nbreVentesParMois),
            'dates_mois' => json_encode($datesMois),

            'date1' => $date1,
            'date2' => $date2,

            'lieux' => $lieuVenteRep->findAll(),

            'search' => $productsRep->find($search),
        ]);
    }
}
