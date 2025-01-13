<?php

namespace App\Controller\Logescom\Achat;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\LieuxVentes;
use App\Entity\Decaissement;
use App\Form\DecaissementType;
use App\Entity\MouvementCaisse;
use App\Entity\AchatFournisseur;
use App\Entity\MouvementProduct;
use App\Entity\CategorieOperation;
use App\Form\AchatFournisseurType;
use App\Repository\UserRepository;
use App\Repository\StockRepository;
use App\Repository\CaisseRepository;
use App\Repository\ClientRepository;
use App\Repository\DeviseRepository;
use App\Entity\ListeTransfertProduct;
use App\Entity\MouvementCollaborateur;
use App\Entity\RetourProductFournisseur;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use App\Repository\ModePaiementRepository;
use App\Entity\ListeProductAchatFournisseur;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\AchatFournisseurRepository;
use App\Repository\MouvementProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Entity\ListeProductAchatFournisseurFrais;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Repository\CategorieDecaissementRepository;
use App\Entity\ListeProductAchatFournisseurMultiple;
use App\Repository\MouvementCollaborateurRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\ListeProductAchatFournisseurRepository;
use App\Repository\ListeProductAchatFournisseurFraisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ListeProductAchatFournisseurMultipleRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/logescom/achat/achat/fournisseur')]
class AchatFournisseurController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_index', methods: ['GET'])]
    public function index(LieuxVentes $lieu_vente, Request $request, UserRepository $userRep, AchatFournisseurRepository $achatFournisseurRepository, EntrepriseRepository $entrepriseRep): Response
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
        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("id_client_search")){
            $achats = $achatFournisseurRepository->findAchatByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 25);
        }else{
            $achats = $achatFournisseurRepository->findAchatByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 25);
        }
        return $this->render('logescom/achat/achat_fournisseur/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'achat_fournisseurs' => $achats,
            'search' => $search,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_new', methods: ['GET', 'POST'])]
    public function new(LieuxVentes $lieu_vente, Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep, CategorieDecaissementRepository $categorieDecRep, CategorieOperationRepository $catetgorieOpRep, CompteOperationRepository $compteOpRep, MouvementCaisseRepository $mouvementRep): Response
    {
        $achatFournisseur = new AchatFournisseur();
        $form = $this->createForm(AchatFournisseurType::class, $achatFournisseur, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        $decaissement = new Decaissement();
        $form_decaissement = $this->createForm(DecaissementType::class, $decaissement, ['lieu_vente' => $lieu_vente, 'decaissement' => $decaissement]);
        $form_decaissement->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant = floatval($montantString);

            $montantString = $form->get('tva')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant_tva = floatval($montantString);

            $achatFournisseur->setLieuVente($lieu_vente)
                        ->setMontant($montant)
                        ->setTva($montant_tva)
                        ->setPersonnel($this->getUser())
                        ->setEtatPaiement("non payé")
                        ->setDateSaisie(new \DateTime("now"));

            $document = $form->get("document")->getData();
            if ($document) {
                $nomFichier= pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$document->guessExtension();
                $document->move($this->getParameter("dossier_achat"),$nouveauNomFichier);
                $achatFournisseur->setDocument($nouveauNomFichier);
            }

            $mouvement_collab_achat = new MouvementCollaborateur();
            $mouvement_collab_achat->setCollaborateur($form->getViewData()->getFournisseur())
                            ->setOrigine("achat-fournisseur")
                            ->setMontant($montant)
                            ->setDevise($form->getViewData()->getDevise())
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser())
                            ->setDateOperation($form->getViewData()->getDateFacture())
                            ->setDateSaisie(new \DateTime("now"));
            $achatFournisseur->addMouvementCollaborateur($mouvement_collab_achat);

            if ($request->get("etat_facture") == 'payé') {
                $caisse = $form_decaissement->getViewData()->getCompteDecaisser();
                $devise = $form->getViewData()->getDevise();
                $solde_caisse = $mouvementRep->findSoldeCaisse($caisse, $devise);
                if ($solde_caisse >= $montant) {
                    $categorie_dec = $categorieDecRep->find(2);
                    $decaissement->setClient($form->getViewData()->getFournisseur())
                                ->setLieuVente($lieu_vente)
                                ->setTraitePar($this->getUser())
                                ->setReference($form->getViewData()->getNumeroFacture())
                                ->setMontant($montant)
                                ->setCompteDecaisser($caisse)
                                ->setModePaie($form_decaissement->getViewData()->getModePaie())
                                ->setDevise($form->getViewData()->getDevise())
                                ->setCommentaire($form->getViewData()->getCommentaire())
                                ->setCategorie($categorie_dec)
                                ->setDateSaisie(new \DateTime("now"));

                    $mouvement_collab = new MouvementCollaborateur();
                    $mouvement_collab->setCollaborateur($form->getViewData()->getFournisseur())
                                    ->setOrigine("decaissement")
                                    ->setMontant(-$montant)
                                    ->setDevise($form->getViewData()->getDevise())
                                    ->setCaisse($form_decaissement->getViewData()->getCompteDecaisser())
                                    ->setLieuVente($lieu_vente)
                                    ->setTraitePar($this->getUser())
                                    ->setDateOperation($form_decaissement->getViewData()->getDateDecaissement())
                                    ->setDateSaisie(new \DateTime("now"));
                    $decaissement->addMouvementCollaborateur($mouvement_collab);

                    $mouvement_caisse = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(3);
                    $compte_op = $compteOpRep->find(1);
                    $mouvement_caisse->setCategorieOperation($categorie_op)
                                    ->setCompteOperation($compte_op)
                                    ->setTypeMouvement("decaissement")
                                    ->setMontant(-$montant)
                                    ->setSaisiePar($this->getUser())
                                    ->setDevise($form->getViewData()->getDevise())
                                    ->setCaisse($form_decaissement->getViewData()->getCompteDecaisser())
                                    ->setLieuVente($lieu_vente)
                                    ->setModePaie($form_decaissement->getViewData()->getModePaie())
                                    ->setDateOperation($form_decaissement->getViewData()->getDateDecaissement())
                                    ->setDateSaisie(new \DateTime("now"));
                    $decaissement->addMouvementCaiss($mouvement_caisse);
                    $entityManager->persist($decaissement);
                    $achatFournisseur->setEtatPaiement("payé");
                }else{
                    $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                    // Récupérer l'URL de la page précédente
                    $referer = $request->headers->get('referer');
                    if ($referer) {
                        $formView = $form->createView();
                        $formDecView = $form_decaissement->createView();
                        return $this->render('logescom/achat/achat_fournisseur/new.html.twig', [
                            'entreprise' => $entrepriseRep->find(1),
                            'lieu_vente' => $lieu_vente,
                            'achat_fournisseur' => $achatFournisseur,
                            'form' => $formView,
                            'decaissement' => $decaissement,
                            'form_decaissement'  => $formDecView,
                            'referer' => $referer,
                        ]);
                    }
                }
            }
            $entityManager->persist($achatFournisseur);
            $entityManager->flush();
            $this->addFlash("success", "Facture ajoutée avec succès. 😊 ");
            return $this->redirectToRoute('app_logescom_achat_achat_fournisseur_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/achat/achat_fournisseur/new.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'achat_fournisseur' => $achatFournisseur,
            'form' => $form,
            'decaissement' => $decaissement,
            'form_decaissement'  => $form_decaissement
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_show', methods: ['GET'])]
    public function show(AchatFournisseur $achatFournisseur, ListeProductAchatFournisseurRepository $listeProductAchatRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $liste_receptions = $listeProductAchatRep->findBy(['achatFournisseur' => $achatFournisseur], ['id' => 'DESC']); 
        return $this->render('logescom/achat/achat_fournisseur/show.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'achat_fournisseur' => $achatFournisseur,
            'liste_receptions' => $liste_receptions,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AchatFournisseur $achatFournisseur, MouvementCollaborateurRepository $mouvementCollabRep, LieuxVentes $lieu_vente, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        
        $form = $this->createForm(AchatFournisseurType::class, $achatFournisseur, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant = floatval($montantString);

            $montantString = $form->get('tva')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant_tva = floatval($montantString);

            $achatFournisseur->setMontant($montant)
                            ->setTva($montant_tva);
            $document =$form->get("document")->getData();
            if ($document) {
                if ($achatFournisseur->getDocument()) {
                    $ancienDiplome=$this->getParameter("dossier_achat")."/".$achatFournisseur->getDocument();
                    if (file_exists($ancienDiplome)) {
                        unlink($ancienDiplome);
                    }
                }
                $nomDiplome= pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomDiplome = $slugger->slug($nomDiplome);
                $nouveauNomDiplome .="_".uniqid();
                $nouveauNomDiplome .= "." .$document->guessExtension();
                $document->move($this->getParameter("dossier_diplome"),$nouveauNomDiplome);
                $achatFournisseur->setDocument($nouveauNomDiplome);

            }

            $mouvement_collab_achat = $mouvementCollabRep->findOneBy(['achatFournisseur' => $achatFournisseur]);
            
            if ($mouvement_collab_achat) {
                $mouvement_collab_achat->setCollaborateur($form->getViewData()->getFournisseur())
                                ->setMontant($montant)
                                ->setDevise($form->getViewData()->getDevise())
                                ->setTraitePar($this->getUser())
                                ->setDateOperation($form->getViewData()->getDateFacture())
                                ->setDateSaisie(new \DateTime("now"));
            }

            $mouvement_collab_facture = $mouvementCollabRep->findOneBy(['factureFrais' => $achatFournisseur]);
            
            if ($mouvement_collab_facture) {
                $mouvement_collab_facture->setCollaborateur($form->getViewData()->getFournisseur())
                                ->setMontant($montant)
                                ->setDevise($form->getViewData()->getDevise())
                                ->setTraitePar($this->getUser())
                                ->setDateOperation($form->getViewData()->getDateFacture());
            }
            $entityManager->flush();
            $this->addFlash("success", "Facture modifiée avec succès. 😊 ");
            return $this->redirectToRoute('app_logescom_achat_achat_fournisseur_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/achat/achat_fournisseur/edit.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'achat_fournisseur' => $achatFournisseur,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, AchatFournisseur $achatFournisseur, LieuxVentes $lieu_vente, Filesystem $filesystem, StockRepository $stockRep, ListeProductAchatFournisseurRepository $listeProductAchatRep, ListeProductAchatFournisseurMultipleRepository $livraisonMultipleRep, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$achatFournisseur->getId(), $request->request->get('_token'))) {
            $products = $listeProductAchatRep->findBy(['achatFournisseur' => $achatFournisseur]);
            
            $stock_exist = true;
            foreach ($products as $value) {
                $stock_livre = $value->getStock();
                if (!$stock_livre) {
                    $stock_exist = false;
                    break;
                }
            }
            $justificatif = $achatFournisseur->getDocument();
            $pdfPath = $this->getParameter("dossier_achat") . '/' . $justificatif;
            // Si le chemin du justificatif existe, supprimez également le fichier
            if ($justificatif && $filesystem->exists($pdfPath)) {
                $filesystem->remove($pdfPath);
            }
            if ($stock_exist) {
    
                // gestion de stock si des produits ont déjà été receptionnés            
                foreach ($products as $productAchat) {                
                    $stock = $stockRep->findOneBy(['product' => $productAchat->getProduct(), 'stockProduit' => $productAchat->getStock() ]);
                    // remise du prix de revient initial
    
                    $qtite_appro = $productAchat->getQuantite();
                    $qtite_init = $stock->getQuantite() - $qtite_appro;
                    $prix_revient_appro = $productAchat->getPrixRevient();
                    $prix_revient_actuel = $stock->getPrixRevient();
    
                    if ($qtite_init != 0 and !empty($qtite_init)) {
                        $prix_initial = (($prix_revient_actuel * ($qtite_appro + $qtite_init)) - ($qtite_appro * $prix_revient_appro)) / $qtite_init ;
    
                        $stock->setPrixRevient($prix_initial);
    
                    }
    
                    $stock->setQuantite($stock->getQuantite() - $productAchat->getQuantite());
                    $entityManager->persist($stock);
                }
    
                
            }else{
                $livraisonsMultiple = $livraisonMultipleRep->findBy(['listeProductAchat' => $products]);
                
                foreach ($livraisonsMultiple as $productMultiple) {                
                    $stock = $stockRep->findOneBy(['product' => $productMultiple->getListeProductAchat()->getProduct(), 'stockProduit' => $productMultiple->getStock() ]);
                    // remise du prix de revient initial
    
                    $qtite_appro = $productMultiple->getListeProductAchat()->getQuantite();
                    $qtite_init = $stock->getQuantite() - $qtite_appro;
                    $prix_revient_appro = $productMultiple->getListeProductAchat()->getPrixRevient();
                    $prix_revient_actuel = $stock->getPrixRevient();
    
                    if ($qtite_init != 0 and !empty($qtite_init)) {
                        $prix_initial = (($prix_revient_actuel * ($qtite_appro + $qtite_init)) - ($qtite_appro * $prix_revient_appro)) / $qtite_init ;
    
                        $stock->setPrixRevient($prix_initial);
    
                    }
    
                    $stock->setQuantite($stock->getQuantite() - $productMultiple->getQuantite());
                    $entityManager->persist($stock);
                }
            }

            $entityManager->remove($achatFournisseur);
            $entityManager->flush();
        }
        $this->addFlash("success", "Facture supprimée avec succès. 😊 ");
        return $this->redirectToRoute('app_logescom_achat_achat_fournisseur_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reception/{id}/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_reception')]
    public function receptionFacture(AchatFournisseur $achatFournisseur, LieuxVentes $lieu_vente, Request $request, ListeStockRepository $listeStockRep, StockRepository $stockRep,  EntrepriseRepository $entrepriseRep, MouvementProductRepository $mouvementProductRep, AuthorizationCheckerInterface $authorizationChecker, ListeProductAchatFournisseurRepository $listeProductAchatRep, EntityManagerInterface $em): Response
    {
        if ($request->get("reception") and $request->get("id_stock")){
            if ($request->get("quantite")) {
                $quantite = $request->get("quantite");
            }else{
                $quantite = 0;
            }

            $stock_product = $stockRep->find($request->get("id_stock"));
            $taux = $achatFournisseur->getTaux();
            
            $taux = str_replace(' ', '', $taux);
            $prix_achat = str_replace(' ', '', $request->get("prix_achat"));
            $prix_vente = str_replace(' ', '', $request->get("prix_vente"));
            $prix_revient = str_replace(' ', '', $request->get("prix_revient"));

            $prix_achat = $prix_achat*$taux;

            // $prix_revient = $prix_revient ? $prix_revient*$taux : $stock_product->getPrixRevient();
            $prix_revient = $prix_revient ? $prix_revient : $stock_product->getPrixRevient();

            // $prix_vente = $prix_vente ? $prix_vente*$taux : $stock_product->getPrixVente();
            $prix_vente = $prix_vente ? $prix_vente : $stock_product->getPrixVente();

            $peremption = str_replace(' ', '', $request->get("peremption"));
            $commentaire = $request->get("commentaire");
            $product_achat = new ListeProductAchatFournisseur();

            if (($quantite + $stock_product->getQuantite())) {
                $prix_revient_moyen =(($prix_revient * $quantite ) + ( $stock_product->getPrixRevient() * $stock_product->getQuantite())) / ($quantite + $stock_product->getQuantite());
                $stock_product->setPrixRevient($prix_revient_moyen);
            }

            $product_achat->setAchatFournisseur($achatFournisseur)
                        ->setQuantite($quantite)
                        ->setProduct($stock_product->getProducts())
                        ->setTaux($taux)
                        ->setPrixAchat($prix_achat)
                        ->setPrixRevient($prix_revient)
                        ->setStock($stock_product->getStockProduit())
                        ->setCommentaire($commentaire)
                        ->setPersonnel($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

            $stock_product->setPrixVente($prix_vente)
                ->setQuantite($stock_product->getQuantite() + $quantite)
                ->setPrixAchat($prix_achat);

            if ($peremption) {
                $stock_product->setDatePeremption($peremption ? (new \DateTime($peremption)) : '');
            }
            
            // on insère dans le mouvement des produits si quantité different de 0
            if ($quantite != 0) {
                $mouvementProduct = new MouvementProduct();
                $mouvementProduct->setStockProduct($stock_product->getStockProduit())
                    ->setProduct($stock_product->getProducts())
                    ->setPersonnel($this->getUser())
                    ->setQuantite($quantite)
                    ->setClient($achatFournisseur->getFournisseur())
                    ->setLieuVente($stock_product->getStockProduit()->getLieuVente())
                    ->setOrigine("achat-fournisseur")
                    ->setDescription("achat-fournisseur")
                    ->setDateOperation(new \DateTime("now"));
                $product_achat->addMouvementProduct($mouvementProduct);
                $em->persist($mouvementProduct);
            }

            // retour produit si qtite < 0

            if ($quantite < 0) {
                $retour = new RetourProductFournisseur();
                $retour->setLieuVente($lieu_vente)
                        ->setQuantite(-$quantite)
                        ->setPrixAchat($prix_achat)
                        ->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));
                $product_achat->addRetourProductFournisseur($retour);

                $mouvement_collab = new MouvementCollaborateur();
                $montant_retour = $prix_achat*$taux*$quantite;
                $mouvement_collab->setCollaborateur($product_achat->getAchatFournisseur()->getFournisseur())
                                ->setOrigine("achat-fournisseur")
                                ->setMontant($montant_retour)
                                ->setDevise($product_achat->getAchatFournisseur()->getDevise())
                                ->setLieuVente($lieu_vente)
                                ->setTraitePar($this->getUser())
                                ->setDateOperation(new \DateTime("now"))
                                ->setDateSaisie(new \DateTime("now"));
                $retour->addMouvementCollaborateur($mouvement_collab);
                $em->persist($mouvement_collab);
            }

            $em->persist($product_achat);
            $em->persist($stock_product);
            $em->flush(); 
            $this->addFlash("success", "produit réceptionné avec succès :)");
            return new RedirectResponse($this->generateUrl('app_logescom_achat_achat_fournisseur_reception', ['id' => $achatFournisseur->getId(), 'lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)]));           

        }
        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }
        
        $pageEncours = $request->get('pageEncours', 1);
        $pageMouvEncours = $request->get('pageMouvEncours', 1);

        if ($request->get("magasin")){
            $magasin = $listeStockRep->find($request->get("magasin"));
        }else{
            $magasin = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);

        }
        $stocks = $stockRep->findStocksByCodeBarrePaginated($magasin, $search, $pageEncours, 5);
        if (!$stocks['data']) {
            $stocks = $stockRep->findStocksForApproInitPaginated($magasin, $search, $pageEncours, 5); 
        } 
        $liste_receptions = $listeProductAchatRep->findBy(['achatFournisseur' => $achatFournisseur], ['id' => 'DESC']); 

        if ($authorizationChecker->isGranted('ROLE_RESPONSABLE') or $authorizationChecker->isGranted('ROLE_ADMIN')) {
            $listeStocks = $listeStockRep->findAll();
        }else{
            $listeStocks = $listeStockRep->findBy(['lieuVente' => $lieu_vente]);

        }

        return $this->render('logescom/achat/achat_fournisseur/reception.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'liste_stocks' => $listeStocks,
            'liste_receptions' => $liste_receptions,
            'magasin' => $magasin,
            'achat_fournisseur' => $achatFournisseur,
            'search' => $search,
            'stocks' => $stocks,

        ]);
    }

    #[Route('/facture/frais/{id}/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_facture_frais')]
    public function factureFrais(AchatFournisseur $achatFournisseur, LieuxVentes $lieu_vente, Request $request, ListeStockRepository $listeStockRep, StockRepository $stockRep,  EntrepriseRepository $entrepriseRep, MouvementProductRepository $mouvementProductRep, ListeProductAchatFournisseurFraisRepository $listeProductAchatFraisRep, ListeProductAchatFournisseurRepository $listeProductAchatRep, ClientRepository $clientRep, CategorieDecaissementRepository $categorieDecaissementRep, UserRepository $userRep, DeviseRepository $deviseRep, CategorieOperationRepository $catetgorieOpRep, CompteOperationRepository $compteOpRep, ModePaiementRepository $modePaieRep, MouvementCollaborateurRepository $mouvementCollabRep, CaisseRepository $caisseRep, ListeProductAchatFournisseurMultipleRepository $livraisonMultipleRep, AuthorizationCheckerInterface $authorizationChecker, EntityManagerInterface $em): Response
    {
        if ($request->get("ajout") ){
            if ($request->get("quantite")) {
                $quantite = $request->get("quantite");
            }else{
                $quantite = 0;
            }
            $taux = $achatFournisseur->getTaux();            
            $taux = str_replace(' ', '', $taux);
            $prix_achat = str_replace(' ', '', $request->get("prix_achat"));
            $prix_vente = str_replace(' ', '', $request->get("prix_vente"));
            $frais = str_replace(' ', '', $request->get("frais"));
            $prix_achat = $prix_achat;

            $stock_product = $stockRep->find($request->get("id_stock"));
            $prix_vente = $prix_vente ? $prix_vente : $stock_product->getPrixVente();
            $commentaire = $request->get("commentaire");
            $product_achat_frais = new ListeProductAchatFournisseurFrais();
            $product_achat_frais->setAchatFournisseur($achatFournisseur)
                        ->setQuantite($quantite)
                        ->setProduct($stock_product->getProducts())
                        ->setTaux($taux)
                        ->setPrixAchat($prix_achat)
                        ->setFrais($frais ? $frais : 0)
                        ->setPrixVente($prix_vente)
                        ->setCommentaire($commentaire)
                        ->setPersonnel($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

            $em->persist($product_achat_frais);
            $em->flush(); 
            $this->addFlash("success", "produit ajouté avec succès :)");
            return new RedirectResponse($this->generateUrl('app_logescom_achat_achat_fournisseur_facture_frais', ['id' => $achatFournisseur->getId(), 'lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)]));           

        }

        $taux = $achatFournisseur->getTaux();
        if ($request->get("factureId")){            
            $products = $listeProductAchatFraisRep->findBy(['achatFournisseur' => $achatFournisseur]);
            foreach ($products as $product) {
                $quantite = $product->getQuantite();
                $prix_achat = $product->getPrixAchat();
                $frais = $product->getFrais();
                $prix_revient = ($prix_achat * $taux) + $frais;

                if ($request->get('livraison') != 'multiple') {
                    // gestion dans le cas un lieu de livraison est different de multiple
                    $livraison = $listeStockRep->find($request->get("livraison"));                   

                    $stock = $stockRep->findOneBy(['stockProduit' => $livraison, 'product' => $product->getProduct()]);

                    $prix_vente = $product->getPrixVente() ? $product->getPrixVente() : $stock->getPrixRevient();
                    // mise à jour du stock
                    if (($quantite + $stock->getQuantite())) {
                        $prix_revient_moyen =(($prix_revient * $quantite ) + ( $stock->getPrixRevient() * $stock->getQuantite())) / ($quantite + $stock->getQuantite());
                        $stock->setPrixRevient($prix_revient_moyen);
                    }

                    $stock->setPrixVente($prix_vente)
                        ->setPrixAchat($prix_achat * $taux)
                        ->setQuantite($stock->getQuantite() + $quantite);

                    $em->persist($stock);

                    // on ajoute les produits dans la liste des produits achètés

                    $product_achat = new ListeProductAchatFournisseur();

                    $product_achat->setAchatFournisseur($achatFournisseur)
                        ->setQuantite($quantite)
                        ->setQuantiteLivre($quantite)
                        ->setProduct($stock->getProducts())
                        ->setTaux($taux)
                        ->setPrixAchat($prix_achat*$taux)
                        ->setPrixRevient($prix_revient)
                        ->setStock($stock->getStockProduit())
                        ->setCommentaire($product->getCommentaire())
                        ->setPersonnel($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

                    $em->persist($product_achat);

                    // on insère dans le mouvement des produits si quantité different de 0                
                    $mouvementProduct = new MouvementProduct();
                    $mouvementProduct->setStockProduct($livraison)
                        ->setProduct($stock->getProducts())
                        ->setPersonnel($this->getUser())
                        ->setQuantite($quantite)
                        ->setClient($achatFournisseur->getFournisseur())
                        ->setLieuVente($stock->getStockProduit()->getLieuVente())
                        ->setOrigine("achat-fournisseur")
                        ->setDescription("achat-fournisseur")
                        ->setDateOperation(new \DateTime("now"));
                    $product_achat->addMouvementProduct($mouvementProduct);
                    $em->persist($mouvementProduct);
                }else{

                    $product_achat = new ListeProductAchatFournisseur();
    
                    $product_achat->setAchatFournisseur($achatFournisseur)
                        ->setQuantite($quantite)
                        ->setProduct($product->getProduct())
                        ->setTaux($taux)
                        ->setPrixAchat($prix_achat*$taux)
                        ->setPrixRevient($prix_revient)
                        ->setCommentaire($product->getCommentaire())
                        ->setPersonnel($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));    
                    $em->persist($product_achat);
                    
                }
    
                    
            }

            $fournisseur = $achatFournisseur->getFournisseur();
            $montantFacture = $request->get('montantFacture');
            $fraisTotaux = $request->get('fraisTotaux');


            $achatFournisseur->setMontant($montantFacture)
                        ->setEtatReception("receptionner");
            $em->persist($achatFournisseur);


            $mouvement_collab = $mouvementCollabRep->findOneBy(['achatFournisseur' => $achatFournisseur]);

            if ($mouvement_collab) {
                $mouvement_collab_fournisseur = $mouvement_collab;
            }else{

                $mouvement_collab_fournisseur = new MouvementCollaborateur();
            }

            $mouvement_collab_fournisseur->setCollaborateur($fournisseur)
                            ->setOrigine("achat-fournisseur")
                            ->setMontant($montantFacture)
                            ->setDevise($achatFournisseur->getDevise())
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser())
                            ->setDateOperation($achatFournisseur->getDateFacture())
                            ->setDateSaisie(new \DateTime("now"));
            $achatFournisseur->addMouvementCollaborateur($mouvement_collab_fournisseur);
            $em->persist($mouvement_collab_fournisseur);

            $fraisTransport = $request->get('fraisTransport');
            $transporteur = $request->get('transporteur');
            $transporteur = $transporteur ? $userRep->find($request->get('transporteur')) : "";

            if ($transporteur) {
                $mouvement_collab_transporteur = new MouvementCollaborateur();
                $mouvement_collab_transporteur->setCollaborateur($transporteur)
                                ->setOrigine("achat-fournisseur")
                                ->setMontant($fraisTransport)
                                ->setDevise($deviseRep->find(1))
                                ->setLieuVente($lieu_vente)
                                ->setTraitePar($this->getUser())
                                ->setDateOperation($achatFournisseur->getDateFacture())
                                ->setDateSaisie(new \DateTime("now"));
                $achatFournisseur->addMouvementCollaborateur($mouvement_collab_transporteur);
                $em->persist($mouvement_collab_transporteur);
            }

            $fraisTransitaire = $request->get('fraisTransitaire');
            $transitaire = $request->get('transitaire');
            $transitaire = $transitaire ? $userRep->find($request->get('transitaire')) : "";

            if ($transitaire) {
                $mouvement_collab_transitaire = new MouvementCollaborateur();
                $mouvement_collab_transitaire->setCollaborateur($transitaire)
                                ->setOrigine("achat-fournisseur")
                                ->setMontant($fraisTransitaire)
                                ->setDevise($deviseRep->find(1))
                                ->setLieuVente($lieu_vente)
                                ->setTraitePar($this->getUser())
                                ->setDateOperation($achatFournisseur->getDateFacture())
                                ->setDateSaisie(new \DateTime("now"));
                $achatFournisseur->addMouvementCollaborateur($mouvement_collab_transitaire);
                $em->persist($mouvement_collab_transitaire);
            }

            $fraisDouane = $request->get('fraisDouane');
            $douanier = $request->get('douanier');
            $douanier = $douanier ? $userRep->find($request->get('douanier')) : "";

            if ($douanier) {
                $mouvement_collab_douanier = new MouvementCollaborateur();
                $mouvement_collab_douanier->setCollaborateur($douanier)
                                ->setOrigine("achat-fournisseur")
                                ->setMontant($fraisDouane)
                                ->setDevise($deviseRep->find(1))
                                ->setLieuVente($lieu_vente)
                                ->setTraitePar($this->getUser())
                                ->setDateOperation($achatFournisseur->getDateFacture())
                                ->setDateSaisie(new \DateTime("now"));
                $achatFournisseur->addMouvementCollaborateur($mouvement_collab_douanier);
                $em->persist($mouvement_collab_douanier);
            }

            $fraisAutre = $request->get('fraisAutre');
            $decaissement = new Decaissement();
            $categorie_dec = $categorieDecaissementRep->find(2);
            $caisse = $caisseRep->findOneCaisseByLieuByType($lieu_vente, 'caisse');
           
            $decaissement->setClient($fournisseur)
                        ->setLieuVente($lieu_vente)
                        ->setTraitePar($this->getUser())
                        ->setReference($achatFournisseur->getNumeroFacture())
                        ->setMontant($fraisAutre)
                        ->setCompteDecaisser($caisse)
                        ->setModePaie($modePaieRep->find(1))
                        ->setDevise($deviseRep->find(1))
                        ->setCommentaire($achatFournisseur->getCommentaire())
                        ->setCategorie($categorie_dec)
                        ->setDateDecaissement(new \DateTime("now"))
                        ->setDateSaisie(new \DateTime("now"));
                        
                        
            $mouvement_caisse = new MouvementCaisse();
            $categorie_op = $catetgorieOpRep->find(3);
            $compte_op = $compteOpRep->find(1);
            $mouvement_caisse->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("decaissement")
                            ->setMontant(-$fraisAutre)
                            ->setSaisiePar($this->getUser())
                            ->setDevise($deviseRep->find(1))
                            ->setCaisse($caisse)
                            ->setLieuVente($lieu_vente)
                            ->setModePaie($modePaieRep->find(1))
                            ->setDateOperation(new \DateTime("now"))
                            ->setDateSaisie(new \DateTime("now"));
            $decaissement->addMouvementCaiss($mouvement_caisse);
            $em->persist($decaissement);
            $em->persist($mouvement_caisse);
            
            $em->flush(); 
            $this->addFlash("success", "Facture traitéé avec succès :)");
            return new RedirectResponse($this->generateUrl('app_logescom_achat_achat_fournisseur_facture_frais', ['id' => $achatFournisseur->getId(), 'lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)]));           

        }

        if ($request->get("livraison") and $request->get("id_stock")){
            if ($request->get("quantite")) {
                $quantite = $request->get("quantite");
            }else{
                $quantite = 0;
            }
            $listeProductAchat = $listeProductAchatRep->find($request->get("liste_achat"));
            $stockLivraison = $listeStockRep->find($request->get("id_stock"));

            $stock_product = $stockRep->findOneBy(["stockProduit" => $request->get("id_stock"), 'product' => $listeProductAchat->getProduct()]);
            $taux = $achatFournisseur->getTaux();
            
            $taux = str_replace(' ', '', $taux);
            $prix_achat = str_replace(' ', '', $request->get("prix_achat"));
            $prix_vente = str_replace(' ', '', $request->get("prix_vente"));
            $prix_revient = str_replace(' ', '', $request->get("prix_revient"));

            $prix_achat = $prix_achat*$taux;

            // $prix_revient = $prix_revient ? $prix_revient*$taux : $stock_product->getPrixRevient();
            $prix_revient = $prix_revient ? $prix_revient : $stock_product->getPrixRevient();

            // $prix_vente = $prix_vente ? $prix_vente*$taux : $stock_product->getPrixVente();
            $prix_vente = $prix_vente ? $prix_vente : $stock_product->getPrixVente();

            $commentaire = $request->get("commentaire");

            if (($quantite + $stock_product->getQuantite())) {
                $prix_revient_moyen =(($prix_revient * $quantite ) + ( $stock_product->getPrixRevient() * $stock_product->getQuantite())) / ($quantite + $stock_product->getQuantite());
                $stock_product->setPrixRevient($prix_revient_moyen);
            }

            $stock_product->setPrixVente($prix_vente)
                ->setQuantite($stock_product->getQuantite() + $quantite)
                ->setPrixAchat($prix_achat);

            
            // on insère dans le mouvement des produits si quantité different de 0
            if ($quantite != 0) {
                $listeProductAchat->setQuantiteLivre($listeProductAchat->getQuantiteLivre() + $quantite);
                $em->persist($listeProductAchat);
                $livraisonMultiple = new ListeProductAchatFournisseurMultiple();
                $livraisonMultiple->setListeProductAchat($listeProductAchat)
                            ->setQuantite($quantite)
                            ->setStock($stockLivraison)
                            ->setCommentaire($commentaire)
                            ->setPersonnel($this->getUser())
                            ->setDateSaisie(new \DateTime("now"));
                $em->persist($livraisonMultiple);


                $mouvementProduct = new MouvementProduct();
                $mouvementProduct->setStockProduct($stock_product->getStockProduit())
                    ->setProduct($stock_product->getProducts())
                    ->setPersonnel($this->getUser())
                    ->setQuantite($quantite)
                    ->setClient($listeProductAchat->getAchatFournisseur()->getFournisseur())
                    ->setLieuVente($stock_product->getStockProduit()->getLieuVente())
                    ->setOrigine("achat-fournisseur")
                    ->setDescription("achat-fournisseur")
                    ->setDateOperation(new \DateTime("now"));
                $livraisonMultiple->addMouvementProduct($mouvementProduct);
                $em->persist($mouvementProduct);
            }
            $em->persist($stock_product);
            $em->flush(); 
            
            return new RedirectResponse($this->generateUrl('app_logescom_achat_achat_fournisseur_facture_frais', ['id' => $achatFournisseur->getId(), 'lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)]));  

        }
        if ($request->get("search")){
            $search = $request->get("search");
        }else{
            $search = "";
        }
        
        $pageEncours = $request->get('pageEncours', 1);
        $pageMouvEncours = $request->get('pageMouvEncours', 1);

        if ($request->get("magasin")){
            $magasin = $listeStockRep->find($request->get("magasin"));
        }else{
            $magasin = $listeStockRep->findOneBy(['lieuVente' => $lieu_vente]);

        }
        $stocks = $stockRep->findStocksByCodeBarrePaginated($magasin, $search, $pageEncours, 5);
        if (!$stocks['data']) {
            $stocks = $stockRep->findStocksForApproInitPaginated($magasin, $search, $pageEncours, 5); 
        } 
        $liste_ajouts = $listeProductAchatFraisRep->findBy(['achatFournisseur' => $achatFournisseur], ['id' => 'DESC']); 

        if ($authorizationChecker->isGranted('ROLE_RESPONSABLE') or $authorizationChecker->isGranted('ROLE_ADMIN')) {
            $listeStocks = $listeStockRep->findAll();
        }else{
            $listeStocks = $listeStockRep->findBy(['lieuVente' => $lieu_vente]);

        }

        $transporteurs = $clientRep->findClientByTypeByLieu("transporteur", "transporteur", $lieu_vente);
        $transitaires = $clientRep->findClientByTypeByLieu("transitaire", "transitaire", $lieu_vente);
        $douaniers = $clientRep->findClientByTypeByLieu("douanier", "douanier", $lieu_vente);

        $commandes = [];
        $commandes_prod = $listeProductAchatRep->findBy(['achatFournisseur' => $achatFournisseur], ['quantite' => 'ASC']);
        foreach ($commandes_prod as $value) {
            if ($authorizationChecker->isGranted('ROLE_RESPONSABLE') or $authorizationChecker->isGranted('ROLE_ADMIN')) {
                $stockProduct = $stockRep->sumQuantiteByProduct($value->getProduct());

            }else{
                $stockProduct = $stockRep->sumQuantiteByProductByLieu($value->getProduct() , $lieu_vente);

    
            }

            $commandes[] = [
                'vente' => $value,
                'stocks' => $stockProduct
            ];
        }
        
        $livraisons = $livraisonMultipleRep->findBy(['listeProductAchat' => $commandes_prod], ['listeProductAchat' => 'ASC']);
        return $this->render('logescom/achat/achat_fournisseur/facture_frais.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'liste_stocks' => $listeStocks,
            'liste_receptions' => $liste_ajouts,
            'magasin' => $magasin,
            'achat_fournisseur' => $achatFournisseur,
            'search' => $search,
            'stocks' => $stocks,
            'transporteurs' => $transporteurs,
            'transitaires' => $transitaires,
            'douaniers' => $douaniers,
            'commandes' => $commandes,
            'livraisons' => $livraisons,

        ]);
    }

    #[Route('/delete/reception/{id}/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_reception_delete', methods: ['POST', 'GET'])]
    public function deleteReception(ListeProductAchatFournisseur $ProductAchat, LieuxVentes $lieu_vente, StockRepository $stockRep, EntityManagerInterface $entityManager): Response
    {
        $stock = $stockRep->findOneBy(['product' => $ProductAchat->getProduct(), 'stockProduit' => $ProductAchat->getStock() ]);
        // remise du prix de revient initial

        $qtite_appro = $ProductAchat->getQuantite();
        $qtite_init = $stock->getQuantite() - $qtite_appro;
        $prix_revient_appro = $ProductAchat->getPrixRevient();
        $prix_revient_actuel = $stock->getPrixRevient();

        if ($qtite_init != 0 and !empty($qtite_init)) {
            $prix_initial = (($prix_revient_actuel * ($qtite_appro + $qtite_init)) - ($qtite_appro * $prix_revient_appro)) / $qtite_init ;

            $stock->setPrixRevient($prix_initial);

        }

        $stock->setQuantite($stock->getQuantite() - $ProductAchat->getQuantite());
        $entityManager->persist($stock);
        $entityManager->remove($ProductAchat);
        $entityManager->flush();
        $this->addFlash("success", "Réception supprimée avec succès :) ");
        return $this->redirectToRoute('app_logescom_achat_achat_fournisseur_reception', ['id' => $ProductAchat->getAchatFournisseur()->getId(), 'lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete/livraison/multiple/{id}/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_livraison_multiple_delete', methods: ['POST', 'GET'])]
    public function deleteLivraisonMultiple(ListeProductAchatFournisseurMultiple $livraisonMultiple, LieuxVentes $lieu_vente, StockRepository $stockRep, Request $request, EntityManagerInterface $entityManager): Response
    {
        $stock = $stockRep->findOneBy(['product' => $livraisonMultiple->getListeProductAchat()->getProduct(), 'stockProduit' => $livraisonMultiple->getStock() ]);
        // remise du prix de revient initial

        $qtite_appro = $livraisonMultiple->getQuantite();
        $qtite_init = $stock->getQuantite() - $qtite_appro;
        $prix_revient_appro = $livraisonMultiple->getListeProductAchat()->getPrixRevient();
        $prix_revient_actuel = $stock->getPrixRevient();

        if ($qtite_init != 0 and !empty($qtite_init)) {
            $prix_initial = (($prix_revient_actuel * ($qtite_appro + $qtite_init)) - ($qtite_appro * $prix_revient_appro)) / $qtite_init ;

            $stock->setPrixRevient($prix_initial);

        }
        $listeProductAchat = $livraisonMultiple->getListeProductAchat();
        $listeProductAchat->setQuantiteLivre($listeProductAchat->getQuantiteLivre() - $qtite_appro);

        $stock->setQuantite($stock->getQuantite() - $livraisonMultiple->getQuantite());
        $entityManager->persist($listeProductAchat);
        $entityManager->persist($stock);
        $entityManager->remove($livraisonMultiple);
        $entityManager->flush();
        $this->addFlash("success", "Réception supprimée avec succès :) ");
        
        return new RedirectResponse($this->generateUrl('app_logescom_achat_achat_fournisseur_facture_frais', ['id' => $livraisonMultiple->getListeProductAchat()->getAchatFournisseur()->getId(), 'lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)]));  
    }


    #[Route('/delete/facture/frais/ajout/{id}/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_facture_frais_ajout_delete', methods: ['POST', 'GET'])]
    public function deleteAjoutFactureFrais(ListeProductAchatFournisseurFrais $productAchat, LieuxVentes $lieu_vente, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productAchat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($productAchat);
            $entityManager->flush();
            $this->addFlash("success", "Ajout supprimé avec succès :) ");
        }
        return $this->redirectToRoute('app_logescom_achat_achat_fournisseur_facture_frais', ['id' => $productAchat->getAchatFournisseur()->getId(), 'lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('/pdf/reception/{id}/{lieu_vente}', name: 'app_logescom_achat_achat_fournisseur_reception_pdf', methods: ['GET'])]
    public function genererPdfAction(AchatFournisseur $achatFournisseur, LieuxVentes $lieu_vente, ListeProductAchatFournisseurRepository $listeProductRep, MouvementCollaborateurRepository $mouvementCollabRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep)
    {
        $entreprise = $entrepriseRep->findOneBy(['id' => 1]);
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$entreprise->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $liste_products = $listeProductRep->findBy(["achatFournisseur" => $achatFournisseur]);

        $soleCollaborateur = $mouvementCollabRep->findSoldeCollaborateur($achatFournisseur->getFournisseur());

        $html = $this->renderView('logescom/achat/achat_fournisseur/pdf_reception.html.twig', [
            'achat_fournisseur' => $achatFournisseur,
            'liste_receptions' => $liste_products,
            'solde_collaborateur' => $soleCollaborateur,
            'logoPath' => $logoBase64,
            'lieu_vente' => $lieu_vente,
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
            'Content-Disposition' => 'inline; filename="produits_receptionnés.pdf"',
        ]);
    }
}
