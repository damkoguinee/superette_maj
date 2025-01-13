<?php

namespace App\Controller\Logescom\Achat;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\LieuxVentes;
use App\Entity\Decaissement;
use App\Form\DecaissementType;
use App\Entity\MouvementCaisse;
use App\Entity\AchatFournisseur;
use App\Entity\FactureFrais;
use App\Entity\MouvementProduct;
use App\Form\FactureFraisType;
use App\Repository\UserRepository;
use App\Repository\StockRepository;
use App\Entity\ListeTransfertProduct;
use App\Entity\MouvementCollaborateur;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use App\Entity\ListeProductAchatFournisseur;
use App\Entity\RetourProductFournisseur;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\AchatFournisseurRepository;
use App\Repository\MouvementProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Repository\CategorieDecaissementRepository;
use App\Repository\DecaissementRepository;
use App\Repository\FactureFraisRepository;
use App\Repository\MouvementCollaborateurRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\ListeProductAchatFournisseurRepository;
use App\Repository\ModePaiementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/achat/facture/frais')]
class FactureFraisController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_achat_facture_frais_index', methods: ['GET'])]
    public function index(LieuxVentes $lieu_vente, Request $request, UserRepository $userRep, FactureFraisRepository $factureFraisRep, EntrepriseRepository $entrepriseRep): Response
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
            $achats = $factureFraisRep->findFactureFraisByLieuBySearchPaginated($lieu_vente, $search, $date1, $date2, $pageEncours, 25);
        }else{
            $achats = $factureFraisRep->findFactureFraisByLieuPaginated($lieu_vente, $date1, $date2, $pageEncours, 25);
        }
        return $this->render('logescom/achat/facture_frais/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'facture_frais' => $achats,
            'search' => $search,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_achat_facture_frais_new', methods: ['GET', 'POST'])]
    public function new(LieuxVentes $lieu_vente, Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep, CategorieDecaissementRepository $categorieDecaissementRep, CategorieOperationRepository $catetgorieOpRep, DecaissementRepository $decaissementRep, ModePaiementRepository $modePaieRep, CompteOperationRepository $compteOpRep, MouvementCaisseRepository $mouvementRep): Response
    {
        $factureFrais = new FactureFrais();
        $form = $this->createForm(FactureFraisType::class, $factureFrais, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant = floatval($montantString);

            $montantString = $form->get('tva')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant_tva = floatval($montantString);

            $factureFrais->setLieuVente($lieu_vente)
                        ->setMontant($montant)
                        ->setTva($montant_tva)
                        ->setPersonnel($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

            $document = $form->get("document")->getData();
            if ($document) {
                $nomFichier= pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$document->guessExtension();
                $document->move($this->getParameter("dossier_achat"),$nouveauNomFichier);
                $factureFrais->setDocument($nouveauNomFichier);
            }

            $mouvement_collab_facture = new MouvementCollaborateur();
            $mouvement_collab_facture->setCollaborateur($form->getViewData()->getFournisseur())
                            ->setOrigine("facture frais")
                            ->setMontant($montant)
                            ->setDevise($form->getViewData()->getDevise())
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser())
                            ->setDateOperation($form->getViewData()->getDateFacture())
                            ->setDateSaisie(new \DateTime("now"));
            $factureFrais->addMouvementCollaborateur($mouvement_collab_facture);

            $entityManager->persist($factureFrais);

            if ($form->getViewData()->getCaisse()) {
                $dateDuJour = new \DateTime();
                $referenceDate = $dateDuJour->format('ymd');
                $idSuivant =($decaissementRep->findMaxId() + 1);
                $reference = "dec".$referenceDate . sprintf('%04d', $idSuivant);
                $caisse = $form->getViewData()->getCaisse();
                $devise = $form->getViewData()->getDevise();
                $solde_caisse = $mouvementRep->findSoldeCaisse($caisse, $devise);
                if ($solde_caisse >= $montant) {
                    $decaissement = new Decaissement();
                    $decaissement->setClient($form->getViewData()->getFournisseur())
                            ->setMontant($montant)
                            ->setDevise($form->getViewData()->getDevise())
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser())
                            ->setReference($reference)
                            ->setModePaie($modePaieRep->find(1))
                            ->setDateSaisie(new \DateTime("now"))
                            ->setDateDecaissement($form->getViewData()->getDateFacture())
                            ->setCompteDecaisser($form->getViewData()->getCaisse())
                            ->setCommentaire($form->getViewData()->getCommentaire())
                            ->setCategorie($categorieDecaissementRep->find(1));
                    $entityManager->persist($decaissement);

                    $mouvement_collab = new MouvementCollaborateur();
                    $mouvement_collab->setCollaborateur($form->getViewData()->getFournisseur())
                            ->setOrigine("decaissement")
                            ->setMontant(-$montant)
                            ->setDevise($form->getViewData()->getDevise())
                            ->setCaisse($form->getViewData()->getCaisse())
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser())
                            ->setDateOperation($form->getViewData()->getDateFacture())
                            ->setDateSaisie(new \DateTime("now"));
                    $decaissement->addMouvementCollaborateur($mouvement_collab);

                    $entityManager->persist($mouvement_collab);

                    $mouvement_caisse = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(3);
                    $compte_op = $compteOpRep->find(1);
                    $mouvement_caisse->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("decaissement")
                            ->setMontant(-$montant)
                            ->setDevise($form->getViewData()->getDevise())
                            ->setCaisse($form->getViewData()->getCaisse())
                            ->setModePaie($modePaieRep->find(1))
                            ->setLieuVente($lieu_vente)
                            ->setSaisiePar($this->getUser())
                            ->setDateOperation($form->getViewData()->getDateFacture())
                            ->setDateSaisie(new \DateTime("now"));
                    $decaissement->addMouvementCaiss($mouvement_caisse);
                    $entityManager->persist($mouvement_caisse);
                }else {
                    $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                    // Récupérer l'URL de la page précédente
                    $referer = $request->headers->get('referer');
                    if ($referer) {
                        $formView = $form->createView();
                        return $this->render('logescom/achat/facture_frais/new.html.twig', [
                            'entreprise' => $entrepriseRep->find(1),
                            'lieu_vente' => $lieu_vente,
                            'facture_frais' => $factureFrais,
                            'form' => $form,
                        ]);
                    }
                }

            }

            $entityManager->flush();
            $this->addFlash("success", "Facture ajoutée avec succès. 😊 ");
            return $this->redirectToRoute('app_logescom_achat_facture_frais_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/achat/facture_frais/new.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'facture_frais' => $factureFrais,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_achat_facture_frais_show', methods: ['GET'])]
    public function show(factureFrais $factureFrais, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    { 
        return $this->render('logescom/achat/facture_frais/show.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'facture_frais' => $factureFrais,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_achat_facture_frais_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FactureFrais $factureFrais, MouvementCollaborateurRepository $mouvementCollabRep, LieuxVentes $lieu_vente, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        
        $form = $this->createForm(FactureFraisType::class, $factureFrais, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant = floatval($montantString);

            $montantString = $form->get('tva')->getData();
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            $montant_tva = floatval($montantString);

            $factureFrais->setMontant($montant)
                            ->setTva($montant_tva);
            $document =$form->get("document")->getData();
            if ($document) {
                if ($factureFrais->getDocument()) {
                    $ancienDiplome=$this->getParameter("dossier_achat")."/".$factureFrais->getDocument();
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
                $factureFrais->setDocument($nouveauNomDiplome);

            }

            $mouvement_collab_facture = $mouvementCollabRep->findOneBy(['factureFrais' => $factureFrais]);
            $mouvement_collab_facture->setCollaborateur($form->getViewData()->getFournisseur())
                            ->setMontant($montant)
                            ->setDevise($form->getViewData()->getDevise())
                            ->setTraitePar($this->getUser())
                            ->setDateOperation($form->getViewData()->getDateFacture())
                            ->setDateSaisie(new \DateTime("now"));
            $entityManager->flush();
            $this->addFlash("success", "Facture modifiée avec succès. 😊 ");
            return $this->redirectToRoute('app_logescom_achat_facture_frais_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/achat/facture_frais/edit.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'facture_frais' => $factureFrais,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_achat_facture_frais_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, FactureFrais $factureFrais, LieuxVentes $lieu_vente, Filesystem $filesystem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$factureFrais->getId(), $request->request->get('_token'))) {
            $justificatif = $factureFrais->getDocument();
            $pdfPath = $this->getParameter("dossier_achat") . '/' . $justificatif;
            // Si le chemin du justificatif existe, supprimez également le fichier
            if ($justificatif && $filesystem->exists($pdfPath)) {
                $filesystem->remove($pdfPath);
            }
            
            $entityManager->remove($factureFrais);
            $entityManager->flush();
        }
        $this->addFlash("success", "Facture frais supprimée avec succès. 😊 ");
        return $this->redirectToRoute('app_logescom_achat_facture_frais_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}
