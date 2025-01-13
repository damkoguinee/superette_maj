<?php

namespace App\Controller\Logescom\Client;

use App\Entity\User;
use App\Entity\Client;
use App\Form\UserType;
use App\Form\ClientType;
use App\Entity\LieuxVentes;
use App\Entity\AjustementSolde;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use App\Repository\DeviseRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\EntrepriseRepository;
use App\Repository\MouvementCollaborateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface as Hasher;

#[Route('/logescom/client/client')]
class ClientController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_client_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository, LieuxVentes $lieu_vente, Request $request, UserRepository $userRep, EntityManagerInterface $em, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("id_client_search")){
            $search = $request->get("id_client_search");
        }else{
            $search = "";
        }

        if ($request->isXmlHttpRequest()) {
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
        if ($request->get("id_client_search")){
            $clients = $clientRepository->findBy(['user' => $search ]);
        }else{

            $clients = $clientRepository->findAllClientByLieu($lieu_vente);
        }
        return $this->render('logescom/client/client/index.html.twig', [
            'clients' => $clients,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
        ]);
    }

    #[Route('/inactif/{lieu_vente}', name: 'app_logescom_client_client_inactif', methods: ['GET'])]
    public function clientInactif(ClientRepository $clientRepository, LieuxVentes $lieu_vente, Request $request, UserRepository $userRep, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("id_client_search")){
            $search = $request->get("id_client_search");
        }else{
            $search = "";
        }

        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $clients = $userRep->findClientInactifSearchByLieu($search, $lieu_vente);    
            $response = [];
            foreach ($clients as $client) {
                $response[] = [
                    'nom' => ucwords($client->getPrenom())." ".strtoupper($client->getNom()),
                    'id' => $client->getId()
                ]; // Mettez à jour avec le nom réel de votre propriété
            }
            return new JsonResponse($response);
        }
        if ($request->get("id_client_search")){
            $clients = $clientRepository->findBy(['user' => $search ]);
        }else{
            $clients = $clientRepository->findAllClientInactifByLieu($lieu_vente);
        }
        return $this->render('logescom/client/client/client_inactif.html.twig', [
            'clients' => $clients,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_client_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, DeviseRepository $deviseRep, UserRepository $userRep, EntrepriseRepository $entrepriseRep, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        $user = new User();
        $form_user = $this->createForm(UserType::class, $user);
        $form_user->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('ymd');
            $idSuivant =($userRep->findMaxId() + 1);
            $reference = "c".$referenceDate . sprintf('%04d', $idSuivant);
            $user->setTypeUser("client")
                ->setDateCreation(new \DateTime("now"))
                ->setReference($reference)
                ->setLieuVente($lieu_vente)
                ->setStatut("actif")
                ->setRoles(["ROLE_CLIENT"])
                ->setPassword(
                    $userPasswordHasher->hashPassword(
                    $user,
                    $form_user->get('password')->getData()
                )
            );
            $limitCredit = $form->get('limitCredit')->getData() ? $form->get('limitCredit')->getData() : 1000000000;
            $client->setRattachement($lieu_vente)
                ->setLimitCredit($limitCredit);
            $user->addClient($client);
            $fichier = $form->get("document")->getData();
            if ($fichier) {
                $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$fichier->guessExtension();
                $fichier->move($this->getParameter("dossier_client"),$nouveauNomFichier);
                $client->setDocument($nouveauNomFichier);
            }
            

            $entityManager->persist($user);
            $entityManager->flush();

            $user = $userRep->findMaxId();
            $user = $userRep->find($user);
            $devises = $deviseRep->findAll();
            foreach ($devises as $key => $devise) {
                $solde = $request->get($devise->getId());
                if ($solde) {
                    $ajustement = new AjustementSolde();
                    $ajustement->setCollaborateur($user)
                            ->setCommentaire("solde initial")
                            ->setMontant($solde)
                            ->setDevise($devise)
                            ->setDateOperation(new \DateTime("now"))
                            ->setDateSaisie(new \DateTime("now"))
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser());

                    $mouvement_collab = new MouvementCollaborateur();
                    $mouvement_collab->setCollaborateur($user)
                            ->setOrigine("solde initial")
                            ->setMontant($solde)
                            ->setDevise($devise)
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser())
                            ->setDateOperation(new \DateTime("now"))
                            ->setDateSaisie(new \DateTime("now"));
                    $ajustement->addMouvementCollaborateur($mouvement_collab);
                    $entityManager->persist($ajustement);
                }
            }
            $entityManager->flush();
            $this->addFlash("success","Collaborateur ajouté avec succès :)");
            if ($request->get('retour')) {
                return new RedirectResponse($request->get('retour'));
            }else{
                return $this->redirectToRoute('app_logescom_client_client_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }
            
        }
        $referer = $request->headers->get('referer');
        return $this->render('logescom/client/client/new.html.twig', [
            'client' => $client,
            'form' => $form,
            'user'  => $user,
            'form_user' => $form_user,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'devises' => $deviseRep->findAll(),
            'referer' => $referer,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_client_client_show', methods: ['GET'])]
    public function show(Client $client, LieuxVentes $lieu_vente, MouvementCollaborateurRepository $mouvementCollabRep, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/client/client/show.html.twig', [
            'client' => $client,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'mouvement' => $mouvementCollabRep->verifMouvement($client),
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_client_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager, DeviseRepository $deviseRep, Hasher $hasher, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        $user = $client->getUser();
        $form_user = $this->createForm(UserType::class, $user);
        $form_user->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $mdp=$form_user->get("password")->getData();
            if ($mdp) {
                $mdpHashe=$hasher->hashPassword($user, $mdp);
                $user->setPassword($mdpHashe);
            }
            $document = $form->get("document")->getData();
            if ($document) {
                if ($client->getDocument()) {
                    $ancien=$this->getParameter("dossier_client")."/".$client->getDocument();
                    if (file_exists($ancien)) {
                        unlink($ancien);
                    }
                }
                $nom= pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNom = $slugger->slug($nom);
                $nouveauNom .="_".uniqid();
                $nouveauNom .= "." .$document->guessExtension();
                $document->move($this->getParameter("dossier_client"),$nouveauNom);
                $client->setDocument($nouveauNom);

            }
            $client->setRattachement($user->getLieuVente());
            $entityManager->persist($user);
            $entityManager->persist($client);
            $entityManager->flush();
            $this->addFlash("success","Client modifié avec succès");
            return $this->redirectToRoute('app_logescom_client_client_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        $referer = $request->headers->get('referer');
        return $this->render('logescom/client/client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'user'  => $client->getUser(),
            'form_user' => $form_user,
            'devises' => $deviseRep->findAll(),
            'referer' => $referer
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_client_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, UserRepository $userRep, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $user =  $userRep->findOneBy(['id' => $client->getUser()]);
            $entityManager->remove($user);
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_client_client_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}
