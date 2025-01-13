<?php

namespace App\Controller\Logescom\Compte;

use App\Entity\LieuxVentes;
use App\Entity\AjustementSolde;
use App\Form\AjustementSoldeType;
use App\Repository\UserRepository;
use App\Repository\DeviseRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AjustementSoldeRepository;
use App\Repository\CompteOperationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\MouvementCollaborateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/compte/ajustement/solde')]
class AjustementSoldeController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_compte_ajustement_solde_index', methods: ['GET'])]
    public function index(AjustementSoldeRepository $ajustementRep, UserRepository $userRep, Request $request, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
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

        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
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
        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("id_client_search")){
            $ajustements = $ajustementRep->findAjustementByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 15);
        }else{
            $ajustements = $ajustementRep->findAjustementByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 15);
        }
        return $this->render('logescom/compte/ajustement_solde/index.html.twig', [
            'ajustement_soldes' => $ajustements,
            'search' => $search,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_compte_ajustement_solde_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, AjustementSoldeRepository $ajustementRep, CompteOperationRepository $compteOpRep, CategorieOperationRepository $catetgorieOpRep, DeviseRepository $deviseRep, MouvementCollaborateurRepository $mouvementCollabRep, UserRepository $userRep, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $clients = $userRep->findUserSearchByLieu($search, $lieu_vente);    
            $response = [];
            foreach ($clients as $client) {
                $response[] = [
                    'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                    'id' => $client->getId()
                ]; 
            }
            return new JsonResponse($response);
        }

        $ajustementSolde = new AjustementSolde();
        $form = $this->createForm(AjustementSoldeType::class, $ajustementSolde);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant = floatval($montantString);
            $client = $request->get('client');
            $client = $userRep->find($client);

            $ajustementSolde->setLieuVente($lieu_vente)
                        ->setCollaborateur($client)
                        ->setTraitePar($this->getUser())
                        ->setMontant($montant)
                        ->setDateSaisie(new \DateTime("now"));

            $mouvement_collab = new MouvementCollaborateur();
            $mouvement_collab->setCollaborateur($client)
                    ->setOrigine("ajustement")
                    ->setMontant($montant)
                    ->setDevise($form->getViewData()->getDevise())
                    ->setLieuVente($lieu_vente)
                    ->setTraitePar($this->getUser())
                    ->setDateOperation($form->getViewData()->getDateOperation())
                    ->setDateSaisie(new \DateTime("now"));
            $ajustementSolde->addMouvementCollaborateur($mouvement_collab);
            $entityManager->persist($ajustementSolde);
            $entityManager->flush();
            $this->addFlash("success", "Ajustement ajouté avec succès :)");
            return $this->redirectToRoute('app_logescom_compte_ajustement_solde_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($request->get("id_client_search")){
            $client_find = $userRep->find($request->get("id_client_search"));
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }else{
            $client_find = array();
            $soldes_collaborateur = array();
        }

        return $this->render('logescom/compte/ajustement_solde/new.html.twig', [
            'ajustement_solde' => $ajustementSolde,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'client_find' => $client_find,
            'soldes_collaborateur' => $soldes_collaborateur,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_compte_ajustement_solde_show', methods: ['GET'])]
    public function show(AjustementSolde $ajustementSolde, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/compte/ajustement_solde/show.html.twig', [
            'ajustement_solde' => $ajustementSolde,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_compte_ajustement_solde_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AjustementSolde $ajustementSolde, AjustementSoldeRepository $ajustementRep, EntityManagerInterface $entityManager, UserRepository $userRep, MouvementCollaborateurRepository $mouvementCollabRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
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

        $form = $this->createForm(AjustementSoldeType::class, $ajustementSolde);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant = floatval($montantString);
            $client = $request->get('client');
            $client = $userRep->find($client);

            $ajustementSolde->setLieuVente($lieu_vente)
                        ->setCollaborateur($client)
                        ->setTraitePar($this->getUser())
                        ->setMontant($montant)
                        ->setDateSaisie(new \DateTime("now"));

            $mouvement_collab = $mouvementCollabRep->findOneBy(['ajustement' => $ajustementSolde]);
            $mouvement_collab->setCollaborateur($client)
                    ->setOrigine("ajustement")
                    ->setMontant($montant)
                    ->setDevise($form->getViewData()->getDevise())
                    ->setLieuVente($lieu_vente)
                    ->setTraitePar($this->getUser())
                    ->setDateOperation($form->getViewData()->getDateOperation())
                    ->setDateSaisie(new \DateTime("now"));

            $entityManager->persist($ajustementSolde);
            $entityManager->flush();

            $this->addFlash("success", "Ajustement modifié avec succès :)");
            return $this->redirectToRoute('app_logescom_compte_ajustement_solde_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($request->get("id_client_search")){
            $client_find = $userRep->find($request->get("id_client_search"));
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }else{
            $client_find = $ajustementSolde->getCollaborateur();
            $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($client_find);
        }

        return $this->render('logescom/compte/ajustement_solde/edit.html.twig', [
            'ajustement_solde' => $ajustementSolde,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'client_find' => $client_find,
            'soldes_collaborateur' => $soldes_collaborateur
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_compte_ajustement_solde_delete', methods: ['POST'])]
    public function delete(Request $request, AjustementSolde $ajustementSolde, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ajustementSolde->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ajustementSolde);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_compte_ajustement_solde_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}
