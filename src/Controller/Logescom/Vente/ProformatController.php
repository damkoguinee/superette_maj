<?php

namespace App\Controller\Logescom\Vente;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Proformat;
use App\Entity\LieuxVentes;
use App\Form\ProformatType;
use App\Entity\ProformatProduct;
use App\Repository\UserRepository;
use App\Repository\StockRepository;
use App\Repository\CaisseRepository;
use App\Repository\ClientRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\ProductsRepository;
use App\Repository\ProformatRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use App\Repository\TauxDeviseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProformatProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\MouvementCollaborateurRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Controller\Logescom\Vente\FacturationController;
use App\Entity\ProformatFraisSup;
use App\Repository\DeviseRepository;
use App\Repository\FraisSupRepository;
use App\Repository\ProformatFraisSupRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/logescom/vente/proformat')]
class ProformatController extends AbstractController
{
    #[Route('/{lieu_vente}', name: 'app_logescom_vente_proformat_index', methods: ['GET'])]
    public function index(LieuxVentes $lieu_vente, Request $request, AuthorizationCheckerInterface $authorizationChecker, EntrepriseRepository $entrepriseRep, UserRepository $userRep, ProformatRepository $proformatRepository): Response
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
            $proformats = $proformatRepository->findProformatByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 25);
        }elseif ($request->get("id_personnel")){
            $proformats = $proformatRepository->findProformatByLieuByPersonnelPaginated($lieu_vente, $request->get("id_personnel"), $date1, $date2, $pageEncours, 25);
        }else{
            if (!$authorizationChecker->isGranted('ROLE_GESTIONNAIRE')) {
                $proformats = $proformatRepository->findProformatByLieuByPersonnelPaginated($lieu_vente, $this->getUser(), $date1, $date2, $pageEncours, 25);
            }else{

                $proformats = $proformatRepository->findProformatByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 25);
            }
        }

        return $this->render('logescom/vente/proformat/index.html.twig', [
            'proformats' => $proformats,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'search' => $search,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_vente_proformat_new', methods: ['GET', 'POST'])]
    public function new(LieuxVentes $lieu_vente, SessionInterface $session, ProformatRepository $proformatRep, UserRepository $userRep, ProductsRepository $productRep, FraisSupRepository $fraisSupRep, DeviseRepository $deviseRep, Request $request, EntityManagerInterface $em): Response
    {
        $session_client = $session->get("session_client", '');
        $panier = $session->get("panier", []);
        $frais_sups = $session->get("frais_sup", '');
        if ($panier) {
            $init = 'prof';
            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('y');
            $idSuivant =($proformatRep->findMaxId() + 1);
            $numeroProformat = $init.$referenceDate . sprintf('%04d', $idSuivant);

            $totalProformat =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('totalFacture')));
            $totalProformat = $totalProformat ? $totalProformat : 0;

            $frais =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('frais')));
            $frais = $frais ? $frais : 0 ;

            $montantRemise =  floatval(preg_replace('/[^-0-9,.]/', '', $request->get('montantRemise')));

            $commentaire= $request->get('commentaire') ? ($request->get('commentaire')) : null ;

            $client = $session_client ? $userRep->find($session_client->getId()) : null;

            $devise = $deviseRep->find(1);

            $proformat = new Proformat();
            $proformat->setNumeroProformat($numeroProformat)
                    ->setTotalProformat($totalProformat)
                    ->setDateSaisie(new \DateTime("now"))
                    ->setCommentaire($commentaire)
                    ->setClient($client)
                    ->setSaisiePar($this->getUser())
                    ->setLieuVente($lieu_vente);
            $em->persist($proformat);

            // enregistrement des frais sup
            if ($frais) {
                foreach ($frais_sups as $frais_sup) {
                    if ($frais_sup['type']) {
                        $type = $fraisSupRep->find($frais_sup['type']);
                        $proformatFrais = new ProformatFraisSup();
                        $proformatFrais->setMontant($frais_sup['montant'])
                                ->setDevise($devise)
                                ->setFraisSup($type);
                        $proformat->addProformatFraisSup($proformatFrais);
                        $em->persist($proformatFrais);

                        
                    }
                }
            }

            foreach ($panier as  $item) {
                $proformatProduct = new ProformatProduct();
                $product = $productRep->find($item['stock']->getProducts()->getId());                
                $proformatProduct->setProduct($product)
                        ->setPrixVente($item['prixVente'])
                        ->setRemise($item['remise'])
                        ->setTva($product->getTva())
                        ->setQuantite($item['qtite']);
                $proformat->addProformatProduct($proformatProduct);
                $em->persist($proformatProduct);
            }
            $em->flush();
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
            $id = $proformatRep->findMaxId();

            $this->addFlash("success", "Proformat enregistré avec succès :)"); 
            return $this->redirectToRoute("app_logescom_vente_proformat_show", ['id'=> $id, 'lieu_vente' => $lieu_vente->getId()]);
            dd($proformat);
        }
        return $this->redirectToRoute("app_logescom_vente_facturation_vente", ['lieu_vente' => $lieu_vente->getId()]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_vente_proformat_show', methods: ['GET'])]
    public function show(Proformat $proformat, LieuxVentes $lieu_vente, MouvementCollaborateurRepository $mouvementCollabRep, ProformatProductRepository $proformatProdRep, ProformatFraisSupRepository $fraisRep, EntrepriseRepository $entrepriseRep): Response
    {
        $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($proformat->getClient());

        return $this->render('logescom/vente/proformat/show.html.twig', [
            'proformat' => $proformat,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'soldes_collaborateur' => $soldes_collaborateur,
            'commandes' => $proformatProdRep->findBy(['proformat' => $proformat], ['quantite' => 'ASC']),
            'frais_sups' => $fraisRep->findBy(['proformat' => $proformat]),

        ]);
    }

    #[Route('/{id}/{lieu_vente}/edit', name: 'app_logescom_vente_proformat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Proformat $proformat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProformatType::class, $proformat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_vente_proformat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/vente/proformat/edit.html.twig', [
            'proformat' => $proformat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/{lieu_vente}', name: 'app_logescom_vente_proformat_delete', methods: ['POST'])]
    public function delete(Request $request, LieuxVentes $lieu_vente, Proformat $proformat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$proformat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($proformat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_vente_proformat_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vente/{id}/{lieu_vente}', name: 'app_logescom_vente_proformat_vente')]
    public function proformatVente(Proformat $proformat, Request $request, ProductsRepository $productsRep, SessionInterface $session, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep, ListeStockRepository $listeStockRep, StockRepository $stockRep, ProformatRepository $proformatRep, TauxDeviseRepository $tauxDeviseRep, MouvementCollaborateurRepository $mouvementCollabRep, UrlGeneratorInterface $urlGenerator, ProformatProductRepository $proformatProdRep): Response
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
        $session->remove("session_proformat");

        $session_client = $session->get("session_client", $proformat->getClient());
        
        $session_proformat = $session->get("session_proformat", $proformat);
        $session->set("session_proformat", $session_proformat);

        $panier = $session->get('panier', []);
        $stock_vente = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente, 'type' => 'vente']);
        $proformatProducts = $proformatProdRep->findBy(['proformat' => $proformat]);
        foreach ($proformatProducts as $commande) {
            $stocks_lieu = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);
            $stock_dispo = $stockRep->sumQuantiteProduct($commande->getProduct(), $stocks_lieu);
            $stock_products = $stockRep->findOneBy(['product' => $commande->getProduct(), 'stockProduit' => $stock_vente]);

            $panier[]=["stock"=>$stock_products, "prixVente" => $commande->getPrixVente(), "qtite" => $commande->getQuantite(), 'remise' => null, 'dispo' => $stock_dispo ];
        }

        $session->set("session_client", $session_client);
        $session->set('panier', $panier);
        $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($proformat->getClient());

        return $this->forward('App\Controller\Logescom\Vente\FacturationController::index', [
            'lieu_vente' => $lieu_vente->getId(),
            'proformat' => $session_proformat,
        ]);
    }

    #[Route('/facture/{id}/{lieu_vente}', name: 'app_logescom_vente_proformat_facture')]
    public function facture(Proformat $proformat, LieuxVentes $lieu_vente, ClientRepository $clientRep, ProformatProductRepository $proformatProdRep, CaisseRepository $caisseRep, MouvementCollaborateurRepository $mouvementCollabRep)
    {        
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$lieu_vente->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $commandes = $proformatProdRep->findAllCommand($proformat);

        $soldes = $mouvementCollabRep->findSoldeCollaborateur($proformat->getClient());

        $soldes_date = $mouvementCollabRep->findAncienSoldeCollaborateur($proformat->getClient(), $proformat->getDateSaisie());

        $html = $this->renderView('logescom/vente/proformat/proformat.html.twig', [
            'proformat' => $proformat,
            'commandes' => $commandes,
            'client' => $clientRep->findOneBy(['user' => $proformat->getClient()]),
            'soldes' => $soldes,
            'soldes_date' => $soldes_date,
            'logoPath' => $logoBase64,
            'lieu_vente' => $lieu_vente,
            'caisses_banque' => $caisseRep->findCaisseByLieuByType($lieu_vente, 'banque'), 
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
            'Content-Disposition' => 'inline; filename="proformat.pdf"',
        ]);
    }
}
