<?php

namespace App\Controller\Logescom\Achat;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\LieuxVentes;
use App\Entity\MouvementProduct;
use App\Entity\BonCommandeFournisseur;
use App\Repository\ProductsRepository;
use App\Form\BonCommandeFournisseurType;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ListeProductBonFournisseur;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BonCommandeFournisseurRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\ListeProductBonFournisseurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/achat/bon/commande/fournisseur')]
class BonCommandeFournisseurController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_achat_bon_commande_fournisseur_index', methods: ['GET'])]
    public function index(LieuxVentes $lieu_vente, BonCommandeFournisseurRepository $bonCommandeRep, EntrepriseRepository $entrepriseRep): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_VENDEUR');
        return $this->render('logescom/achat/bon_commande_fournisseur/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'bon_fournisseurs' => $bonCommandeRep->findAll(),
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_achat_bon_commande_fournisseur_new', methods: ['GET', 'POST'])]
    public function new(LieuxVentes $lieu_vente, Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $bonFournisseur = new BonCommandeFournisseur();
        $form = $this->createForm(BonCommandeFournisseurType::class, $bonFournisseur, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            // Supprimez les espaces pour obtenir un nombre valide
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            // Convertissez la chaîne en nombre
            $montant = floatval($montantString);
            $bonFournisseur->setPersonnel($this->getUser())
                        ->setLieuVente($lieu_vente)
                        ->setMontant($montant)
                        ->setDateSaisie(new \DateTime("now"));

            $entityManager->persist($bonFournisseur);
            $entityManager->flush();
            $this->addFlash("success", "Facture ajoutée avec succès. 😊 ");
            return $this->redirectToRoute('app_logescom_achat_bon_commande_fournisseur_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/achat/bon_commande_fournisseur/new.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'achat_fournisseur' => $bonFournisseur,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_achat_bon_commande_fournisseur_show', methods: ['GET'])]
    public function show(BonCommandeFournisseur $bonFournisseur, ListeProductBonFournisseurRepository $lsiteBonRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $liste_receptions = $lsiteBonRep->findBy(['bonFournisseur' => $bonFournisseur], ['id' => 'DESC']); 
        return $this->render('logescom/achat/bon_commande_fournisseur/show.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'bon_fournisseur' => $bonFournisseur,
            'liste_receptions' => $liste_receptions,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_achat_bon_commande_fournisseur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LieuxVentes $lieu_vente, BonCommandeFournisseur $bonCommandeFournisseur, EntrepriseRepository $entrepriseRep, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BonCommandeFournisseurType::class, $bonCommandeFournisseur, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            // Supprimez les espaces pour obtenir un nombre valide
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            // Convertissez la chaîne en nombre
            $montant = floatval($montantString);
            $bonCommandeFournisseur->setMontant($montant);
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_achat_bon_commande_fournisseur_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/achat/bon_commande_fournisseur/edit.html.twig', [
            'bon_fournisseur' => $bonCommandeFournisseur,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_achat_bon_commande_fournisseur_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, BonCommandeFournisseur $bonCommandeFournisseur, LieuxVentes $lieu_vente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bonCommandeFournisseur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($bonCommandeFournisseur);
            $entityManager->flush();
        }

        $this->addFlash("success", "Facture supprimée avec succès. 😊 ");
        return $this->redirectToRoute('app_logescom_achat_bon_commande_fournisseur_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('/ajout/{id}/{lieu_vente}', name: 'app_logescom_achat_bon_commande_fournisseur_ajout')]
    public function approInitial(BonCommandeFournisseur $bonCommande, LieuxVentes $lieu_vente, Request $request, EntrepriseRepository $entrepriseRep, ListeProductBonFournisseurRepository $listeProductBonRep, ListeStockRepository $listeStockRep, ProductsRepository $productRep, EntityManagerInterface $em): Response
    {
        if ($request->get("reception") and $request->get("id_prod")){
            if ($request->get("quantite")) {
                $quantite = $request->get("quantite");
            }else{
                $quantite = 0;
            }
            $prix_achat = str_replace(' ', '', $request->get("prix_achat"));
            $product_bon = new ListeProductBonFournisseur();
            $product = $productRep->find($request->get("id_prod"));

            $product_bon->setBonFournisseur($bonCommande)
                        ->setQuantite($quantite)
                        ->setProduct($product)
                        ->setPrixAchat($prix_achat);
            
            
            $em->persist($product_bon);
            $em->flush(); 
            $this->addFlash("success", "produit ajouté avec succès :)");
            return new RedirectResponse($this->generateUrl('app_logescom_achat_bon_commande_fournisseur_ajout', ['id' => $bonCommande->getId(), 'lieu_vente' => $lieu_vente->getId(), 'search' => $request->get("search"), 'magasin' => $request->get("magasin"), 'pageEncours' => $request->get('pageEncours', 1)]));           

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
        $liste_products = $productRep->findProductsByCodeBarrePaginated($search, $pageEncours, 5);
        if (!$liste_products['data']) {
            $liste_products = $productRep->findProductsForPaginated($search, $pageEncours, 5); 
        } 
        $liste_ajouts = $listeProductBonRep->findBy(['bonFournisseur' => $bonCommande], ['id' => 'DESC']); 

        $listeStocks = $listeStockRep->findBy(['lieuVente' => $lieu_vente]);
        return $this->render('logescom/achat/bon_commande_fournisseur/ajout.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'liste_stocks' => $listeStocks,
            'liste_ajouts' => $liste_ajouts,
            'magasin' => $magasin,
            'achat_fournisseur' => $bonCommande,
            'search' => $search,
            'products' => $liste_products,

        ]);
    }

    #[Route('/delete/ajout/{id}/{lieu_vente}', name: 'app_logescom_achat_bon_commande_fournisseur_ajout_delete', methods: ['POST', 'GET'])]
    public function deleteReception(ListeProductBonFournisseur $ProductBon, LieuxVentes $lieu_vente, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($ProductBon);
        $entityManager->flush();
        $this->addFlash("success", "Réception supprimée avec succès :) ");
        return $this->redirectToRoute('app_logescom_achat_bon_commande_fournisseur_ajout', ['id' => $ProductBon->getBonFournisseur()->getId(), 'lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('/pdf/ajout/{id}/{lieu_vente}', name: 'app_logescom_achat_bon_commande_fournisseur_ajout_pdf', methods: ['GET'])]
    public function genererPdfAction(BonCommandeFournisseur $bonFournisseur, LieuxVentes $lieu_vente, ListeProductBonFournisseurRepository $listeProductRep, EntrepriseRepository $entrepriseRep)
    {
        $entreprise = $entrepriseRep->findOneBy(['id' => 1]);
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$entreprise->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $liste_products = $listeProductRep->findBy(["bonFournisseur" => $bonFournisseur]);

        $html = $this->renderView('logescom/achat/bon_commande_fournisseur/pdf_bon.html.twig', [
            'bon_fournisseur' => $bonFournisseur,
            'liste_products' => $liste_products,
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
            'Content-Disposition' => 'inline; filename="bon_fournisseur.pdf"',
        ]);
    }

    #[Route('/pdf/simple/ajout/{id}/{lieu_vente}', name: 'app_logescom_achat_bon_commande_fournisseur_ajout_pdf_bon', methods: ['GET'])]
    public function bonPdf(BonCommandeFournisseur $bonFournisseur, LieuxVentes $lieu_vente, ListeProductBonFournisseurRepository $listeProductRep, EntrepriseRepository $entrepriseRep)
    {
        $entreprise = $entrepriseRep->findOneBy(['id' => 1]);
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$entreprise->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $liste_products = $listeProductRep->findBy(["bonFournisseur" => $bonFournisseur]);

        $html = $this->renderView('logescom/achat/bon_commande_fournisseur/pdf_bon_simple.html.twig', [
            'bon_fournisseur' => $bonFournisseur,
            'liste_products' => $liste_products,
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
            'Content-Disposition' => 'inline; filename="bon_fournisseur.pdf"',
        ]);
    }
}
