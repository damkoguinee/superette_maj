<?php

namespace App\Controller\Logescom\Compte;

use App\Entity\User;
use App\Entity\Devise;
use App\Entity\Versement;
use App\Entity\LieuxVentes;
use App\Entity\MouvementCaisse;
use App\Repository\UserRepository;
use App\Repository\CaisseRepository;
use App\Repository\ClientRepository;
use App\Repository\DeviseRepository;
use App\Repository\RegionRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\CategorieDecaissementRepository;
use App\Repository\CategorieOperationRepository;
use App\Repository\CompteOperationRepository;
use App\Repository\PersonnelRepository;
use App\Repository\VersementRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ModePaiementRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\ConfigurationLogicielRepository;
use App\Repository\MouvementCollaborateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/compte/compte/collaborateur')]
class CompteCollaborateurController extends AbstractController
{
    #[Route('/{lieu_vente}', name: 'app_logescom_compte_compte_collaborateur')]
    public function index(LieuxVentes $lieu_vente, Request $request, MouvementCollaborateurRepository $mouvementRep, DeviseRepository $deviseRep, ClientRepository $clientRep, UserRepository $userRep, RegionRepository $regionRep, PersonnelRepository $personnelRep, EntrepriseRepository $entrepriseRep): Response
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
        $type1 = $request->get('type1') ? $request->get('type1') : 'client';
        $type2 = $request->get('type2') ? $request->get('type2') : 'client-fournisseur';

        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            if ($request->query->get('search_personnel')) {
                $clients = $userRep->findPersonnelSearchByLieu($search, $lieu_vente);    
            }else{

                $clients = $userRep->findClientSearchByLieu($search, $lieu_vente);    
            }
            $response = [];
            foreach ($clients as $client) {
                $response[] = [
                    'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                    'id' => $client->getId()
                ]; // Mettez à jour avec le nom réel de votre propriété
            }
            return new JsonResponse($response);
        }
        $regions = $regionRep->findBy([], ['nom' => 'ASC']);
        $devises = $deviseRep->findAll();
        
        if (($type1 == 'personnel' and $type2 == 'personnel') or $request->get('id_personnel')) {
            $type1 = 'personnel';
            $type2 = 'personnel';
            if ($request->get("id_personnel")) {
                $clients = $personnelRep->findPersonnelByTypeBySearchByLieu($type1, $type2, $lieu_vente, $request->get("id_personnel"));
            }elseif ($request->get("region")) {
                $clients = $personnelRep->findPersonnelByTypeBySearchByLieuByRegion($type1, $type2, $lieu_vente, $request->get("region"));
            }else{
                $clients = $personnelRep->findPersonnelByTypeByLieu($type1, $type2, $lieu_vente);
    
            }
        }else{

            if ($request->get("id_client_search")) {
                $clients = $clientRep->findClientSearchByTypeByLieu($type1, $type2, $lieu_vente, $request->get("id_client_search"));
            }elseif ($request->get("region")) {
                $clients = $clientRep->findClientSearchByTypeByLieuByRegion($type1, $type2, $lieu_vente, $request->get("region"));
            }else{
                $clients = $clientRep->findClientByTypeByLieu($type1, $type2, $lieu_vente);    
            }
        }
        $comptes = [];
        foreach ($clients as $client) {
           $comptes[] = [
                'collaborateur' => $client->getUser(),
                'soldes' => $mouvementRep->findSoldeCompteCollaborateur($client->getUser(), $devises)
            ];
        }
        // dd($comptes, $clients);

        if ($request->get("region")){
            $region_find = $regionRep->find($request->get("region"));
        }else{
            $region_find = array();
        }
        $solde_general_type = $mouvementRep->findSoldeGeneralByType($type1, $type2, $lieu_vente, $devises);
        // dd($solde_general_type);
        return $this->render('logescom/compte/compte_collaborateur/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
            'comptes' => $comptes,
            'devises'   => $devises,
            'regions' => $regions,
            'region_find' => $region_find,
            'type1' => $type1,
            'type2' => $type2,
            'solde_general_type' => $solde_general_type,
        ]);
    }

    #[Route('/detail/{lieu_vente}', name: 'app_logescom_compte_compte_collaborateur_detail')]
    public function detailCompte(LieuxVentes $lieu_vente, UserRepository $userRep, Request $request, MouvementCollaborateurRepository $mouvementRep, ModePaiementRepository $modePaiementRep, CaisseRepository $caisseRep, ConfigurationLogicielRepository $configRep, VersementRepository $versementRep, EntityManagerInterface $entityManager, CategorieOperationRepository $catetgorieOpRep, CompteOperationRepository $compteOpRep, DeviseRepository $deviseRep, CategorieDecaissementRepository $categorieDecaissementRep, EntrepriseRepository $entrepriseRep): Response
    {
        

        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }


        $user = $userRep->find($request->get('user'));
        $devise = $deviseRep->findOneBy(['nomDevise' => $request->get('devise')]);

        if ($request->get('montant') and $request->get('caisse')) {
            $versement = new Versement();
            $montantString = $request->get('montant');
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval($montantString);
            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('ymd');
            $idSuivant =($versementRep->findMaxId() + 1);
            $reference = "vers".$referenceDate . sprintf('%04d', $idSuivant);
            $client = $user;
            $caisse = $caisseRep->find($request->get('caisse'));
            $mode = $modePaiementRep->find($request->get('modePaie'));
            $date = $request->get('date');

            $versement->setLieuVente($lieu_vente)
                        ->setClient($client)
                        ->setTraitePar($this->getUser())
                        ->setReference($reference)
                        ->setMontant($montant)
                        ->setTaux(1)
                        ->setCommentaire('paiement client')
                        ->setCategorie($categorieDecaissementRep->find(4))
                        ->setDevise($deviseRep->find(1))
                        ->setCompte($caisse)
                        ->setModePaie($mode)
                        ->setDateVersement(new \DateTime($date ? $date : 'now'))
                        ->setDateSaisie(new \DateTime("now"));


            $mouvement_caisse = new MouvementCaisse();
            $categorie_op = $catetgorieOpRep->find(4);
            $compte_op = $compteOpRep->find(4);
            $mouvement_caisse->setCategorieOperation($categorie_op)
                    ->setCompteOperation($compte_op)
                    ->setTypeMouvement("versement")
                    ->setMontant($montant)
                    ->setDevise($deviseRep->find(1))
                    ->setCaisse($caisse)
                    ->setModePaie($mode)
                    ->setNumeroPaie(NULL)
                    ->setBanqueCheque(NULL)
                    ->setLieuVente($lieu_vente)
                    ->setSaisiePar($this->getUser())
                    ->setDateOperation(new \DateTime($date ? $date : 'now'))
                    ->setDateSaisie(new \DateTime("now"));
            $versement->addMouvementCaiss($mouvement_caisse);

            $mouvement_collab = new MouvementCollaborateur();

            $taux = 1;
            if ($taux == 1) {
                $montant = $montant;
                $devise = $deviseRep->find(1);
            }else{
                $montant = $montant * $taux;
                $devise = $deviseRep->find(1);
            }
            $mouvement_collab->setCollaborateur($client)
                    ->setOrigine("versement")
                    ->setMontant($montant)
                    ->setDevise($devise)
                    ->setCaisse($caisse)
                    ->setLieuVente($lieu_vente)
                    ->setTraitePar($this->getUser())
                    ->setDateOperation(new \DateTime($date ? $date : 'now'))
                    ->setDateSaisie(new \DateTime("now"));
            $versement->addMouvementCollaborateur($mouvement_collab);
            
            // dd($versement, $mouvement_caisse, $mouvement_collab);

            $entityManager->persist($versement);
            $entityManager->flush();

                // $this->addFlash("success", "versement enregistré avec succès :)");
                // return $this->redirectToRoute('app_logescom_entrees_versement_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }
        
        $pageEncours = $request->get('pageEncours', 1);

        $mouvements = $mouvementRep->SoldeDetailByCollaborateurByDeviseByDate($user, $devise, $date1, $date2, $pageEncours, 20000);
        
        $solde_init = $mouvementRep->sumMontantBeforeStartDate($user, $devise, $date1);
        $config = $configRep->findOneBy([]);
        $modesPaies = $modePaiementRep->findAll();
        $caisses = $caisseRep->findCaisseByLieu($lieu_vente);
        return $this->render('logescom/compte/compte_collaborateur/detail_compte.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'mouvements' =>$mouvements,
            'solde_init' => $solde_init,
            'user' => $user,
            'devise' => $devise,
            'config' => $config,
            'modesPaies' => $modesPaies,
            'caisses' => $caisses,
        ]);
    }

    #[Route('/inactif/{lieu_vente}', name: 'app_logescom_compte_compte_collaborateur_inactif')]
    public function compteInactif(LieuxVentes $lieu_vente, Request $request, MouvementCollaborateurRepository $mouvementRep, DeviseRepository $deviseRep, ClientRepository $clientRep, UserRepository $userRep, RegionRepository $regionRep, PersonnelRepository $personnelRep, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("id_client_search")){
            $search = $request->get("id_client_search");
        }else{
            $search = "";
        }

        $type1 = $request->get('type1') ? $request->get('type1') : 'client';
        $type2 = $request->get('type2') ? $request->get('type2') : 'client-fournisseur';

        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            if ($request->query->get('search_personnel')) {
                $clients = $userRep->findPersonnelSearchByLieu($search, $lieu_vente);    
            }else{

                $clients = $userRep->findClientSearchByLieu($search, $lieu_vente);    
            }
            $response = [];
            foreach ($clients as $client) {
                $response[] = [
                    'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                    'id' => $client->getId()
                ]; // Mettez à jour avec le nom réel de votre propriété
            }
            return new JsonResponse($response);
        }

        if ($request->get("limit")){
            $limit = $request->get("limit");
        }else{
            $limit = 30;
        } 
        $regions = $regionRep->findBy([], ['nom' => 'ASC']);
        $devises = $deviseRep->findBy(['id' => 1]);
        
        

        if ($request->get("id_client_search")) {
            $clients = $clientRep->findClientSearchByTypeByLieu($type1, $type2, $lieu_vente, $request->get("id_client_search"));

        }elseif ($request->get("region")) {

            $clients = $clientRep->findClientSearchByTypeByLieuByRegion($type1, $type2, $lieu_vente, $request->get("region"));

        }else{
            
            $clients = $clientRep->findClientByTypeByLieu($type1, $type2, $lieu_vente);    
        }
        
        $comptesInactifs = [];
        foreach ($clients as $client) {
            $soldes = $mouvementRep->comptesInactif($client->getUser(), $limit, $devises);
            if ($soldes) {
                $comptesInactifs[] = [
                    'collaborateur' => $client->getUser(),
                    'soldes' => $mouvementRep->comptesInactif($client->getUser(), $limit, $devises),
                    'derniereOp' => $mouvementRep->findOneBy(['collaborateur' => $client->getUser()], ['id' => 'DESC'])
                ];
            }
        }

        if ($request->get("region")){
            $region_find = $regionRep->find($request->get("region"));
        }else{
            $region_find = array();
        }
        return $this->render('logescom/compte/compte_collaborateur/compte_inactif.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
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
}
