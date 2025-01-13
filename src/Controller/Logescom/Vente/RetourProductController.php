<?php

namespace App\Controller\Logescom\Vente;

use App\Entity\Livraison;
use App\Entity\Facturation;
use App\Entity\LieuxVentes;
use App\Entity\RetourProduct;
use App\Entity\CommandeProduct;
use App\Entity\MouvementCaisse;
use App\Entity\MouvementCollaborateur;
use App\Form\RetourProductType;
use App\Entity\MouvementProduct;
use App\Repository\CaisseRepository;
use App\Repository\ProductsRepository;
use App\Repository\LivraisonRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ListeStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\RetourProductRepository;
use App\Repository\CommandeProductRepository;
use App\Repository\CompteOperationRepository;
use App\Repository\FactureFraisSupRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Repository\DeviseRepository;
use App\Repository\ModePaiementRepository;
use App\Repository\ModificationFactureRepository;
use App\Repository\MouvementCollaborateurRepository;
use App\Repository\StockRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/vente/retour/product')]
class RetourProductController extends AbstractController
{
    #[Route('/', name: 'app_logescom_vente_retour_product_index', methods: ['GET'])]
    public function index(RetourProductRepository $retourProductRepository): Response
    {
        return $this->render('logescom/vente/retour_product/index.html.twig', [
            'retour_products' => $retourProductRepository->findAll(),
        ]);
    }

    #[Route('/facture/retour/{id}/{lieu_vente}', name: 'app_logescom_vente_retour_product_facture')]
    public function factureRetour(Facturation $facturation, LieuxVentes $lieu_vente, MouvementCollaborateurRepository $mouvementCollabRep, CommandeProductRepository $commandeProdRep, MouvementCaisseRepository $mouvementCaisseRep, CaisseRepository $caisseRep, LivraisonRepository $livraisonRep, FactureFraisSupRepository $fraisRep, ListeStockRepository $listeStockRep, SessionInterface $session, Request $request, EntrepriseRepository $entrepriseRep): Response
    {
        $soldes_collaborateur = $mouvementCollabRep->findSoldeCollaborateur($facturation->getClient());

        $commandes_prod = $commandeProdRep->findBy(['facturation' => $facturation], ['quantite' => 'ASC']);

        $livraisons = $livraisonRep->findBy(['commande' => $commandes_prod], ['commande' => 'ASC']);

        return $this->render('logescom/vente/retour_product/retour_facture.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'facturation' => $facturation,
            'commandes' => $commandeProdRep->findBy(['facturation' => $facturation], ['quantite' => 'ASC']),
            'caisses' => $mouvementCaisseRep->findBy(['facturation' => $facturation]),
            'frais_sups' => $fraisRep->findBy(['facturation' => $facturation]),
            'soldes_collaborateur' => $soldes_collaborateur,
            'listes_caisses' => $caisseRep->findCaisseByLieu($lieu_vente),
            'livraisons' => $livraisons,
            'liste_stocks' => $listeStockRep->findBy(['lieuVente' => $lieu_vente]),

        ]);
    }

    #[Route('/new/retour/{id}/{lieu_vente}', name: 'app_logescom_vente_retour_product_new', methods: ['GET', 'POST'])]
    public function new(CommandeProduct $commandeProd, LieuxVentes $lieu_vente, CaisseRepository $caisseRep, ListeStockRepository $listeStockRep, RetourProductRepository $retourProdRep, CompteOperationRepository $compteOpRep, CategorieOperationRepository $catetgorieOpRep, DeviseRepository $deviseRep, StockRepository $stockRep, ModePaiementRepository $modePaieRep, Request $request, EntityManagerInterface $em , EntrepriseRepository $entrepriseRep): Response
    {
        
        if ($request->get('qtite_retour') and $request->get('retour')) {
            $qtite_retour =  floatval(preg_replace('/[^0-9,.]/', '', $request->get('qtite_retour')));
            $facturation = $commandeProd->getFacturation();

            if ($qtite_retour <= $commandeProd->getQuantite()) {
               
                $prix_vente =  $commandeProd->getPrixVente();
                $remise = $commandeProd->getRemise();

                $prix_total = $prix_vente * $qtite_retour;

                $date_retour = new \DateTime($request->get('date_retour'));

                $product = $commandeProd->getProduct();

                $caisse = $request->get('caisse');
                $caisse = $caisse ? $caisseRep->find($caisse) : null;

                $emplacement = $request->get('stock');
                $emplacement = $emplacement ? $listeStockRep->find($emplacement) : null;

                $stock = $stockRep->findOneBy(['stockProduit' => $emplacement, 'product' => $product]);
                // on met à jour le stock
                $stock->setQuantite($stock->getQuantite() + $qtite_retour);
                $em->persist($stock);

                

                $commande = new CommandeProduct();
                $commande->setFacturation($facturation)
                        ->setProduct($product)
                        ->setPrixVente($prix_vente)
                        ->setQuantite(-$qtite_retour)
                        ->setRemise((-$qtite_retour * $remise))
                        ->setQuantiteLivre(-$qtite_retour);

                $retourProduct = new RetourProduct();
                $retourProduct->setProduct($product)
                        ->setQuantite($qtite_retour)
                        ->setPrixVente($prix_vente)
                        ->setRemise($remise)
                        ->setCaisse($caisse)
                        ->setStockRetour($emplacement)
                        ->setLieuVente($lieu_vente)
                        ->setSaisiePar($this->getUser())
                        ->setDateRetour($date_retour)
                        ->setDateSaisie(new \DateTime("now"));
                $commande->addRetourProduct($retourProduct);


                $livraison = new Livraison();
                $livraison->setQuantiteLiv(-$qtite_retour)
                        ->setSaisiePar($this->getUser())
                        ->setStock($emplacement)
                        ->setCommentaire('retour produit')
                        ->setDateLivraison($date_retour)
                        ->setDateSaisie(new \DateTime("now"));
                $commande->addLivraison($livraison);

                $mouvementProduct = new MouvementProduct();
                $mouvementProduct->setProduct($product)
                            ->setStockProduct($emplacement)
                            ->setQuantite($qtite_retour)
                            ->setLieuVente($lieu_vente)
                            ->setPersonnel($this->getUser())
                            ->setDateOperation($date_retour)
                            ->setOrigine('retour produit')
                            ->setDescription('retour produit '.$facturation->getNumeroFacture())
                            ->setClient($facturation->getClient());
                $livraison->addMouvementProduct($mouvementProduct);

                $devise = $deviseRep->find(1);
                if ($commande->getFacturation()->getEtat() != 'payé') {
                    $mouvementClient = new MouvementCollaborateur();
                    $mouvementClient->setCollaborateur($facturation->getClient())
                            ->setOrigine("retour produit")
                            ->setMontant($prix_total)
                            ->setDevise($devise)
                            ->setCaisse($caisse)
                            ->setLieuVente($lieu_vente)
                            ->setTraitePar($this->getUser())
                            ->setDateOperation($date_retour)
                            ->setDateSaisie(new \DateTime("now"));
                    $retourProduct->addMouvementCollaborateur($mouvementClient);
                }

                if ($caisse) {
                    $mouvementCaisse = new MouvementCaisse();
                    $mouvementCaisse = new MouvementCaisse();
                    $categorie_op = $catetgorieOpRep->find(3);
                    $compte_op = $compteOpRep->find(1);
                    $mode_paie = $modePaieRep->find(1);
                    $mouvementCaisse->setCategorieOperation($categorie_op)
                            ->setCompteOperation($compte_op)
                            ->setTypeMouvement("facturation")
                            ->setMontant(-$prix_total)
                            ->setDevise($devise)
                            ->setCaisse($caisse)
                            ->setModePaie($mode_paie)
                            ->setLieuVente($lieu_vente)
                            ->setDateOperation($date_retour)
                            ->setSaisiePar($this->getUser())
                            ->setDateSaisie(new \DateTime("now"));
                    $retourProduct->addMouvementCaiss($mouvementCaisse);
                    $em->persist($mouvementCaisse);
                    
                }
                $em->persist($commande);
                $em->persist($livraison);
                if ($commande->getFacturation()->getEtat() != 'payé') {
                    $em->persist($mouvementClient);
                }

                $this->addFlash('success', "Retour éffectué avec succès :)");
                $em->flush();
                return $this->redirectToRoute('app_logescom_vente_retour_product_facture', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash('warning', "vous devez saisir une quantité inférieure ou égale à ".$commandeProd->getQuantite()." :)");
                
                return $this->redirectToRoute('app_logescom_vente_retour_product_facture', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        // gestion annulation retour
        if ($request->get('qtite_retour') and $request->get('annuler')) {

            $qtite_retour =  floatval(preg_replace('/[^0-9,.]/', '', $request->get('qtite_retour')));

            $prix_vente =  $commandeProd->getPrixVente();
            $remise = $commandeProd->getRemise();

            $prix_total = $prix_vente * $qtite_retour;

            $date_retour = new \DateTime($request->get('date_retour'));

            $product = $commandeProd->getProduct();

            $caisse = $request->get('caisse');
            $caisse = $caisse ? $caisseRep->find($caisse) : null;

            $emplacement = $request->get('stock');
            $emplacement = $emplacement ? $listeStockRep->find($emplacement) : null;

            $stock = $stockRep->findOneBy(['stockProduit' => $emplacement, 'product' => $product]);
            // on met à jour le stock
            $stock->setQuantite($stock->getQuantite() - $qtite_retour);
            $em->persist($stock);

            // on reajuste le montant de retour
            $facturation = $commandeProd->getFacturation();
            
            $em->remove($commandeProd);
            
            $this->addFlash('success', "Retour annulé avec succès :)");
            $em->flush();
            return $this->redirectToRoute('app_logescom_vente_retour_product_facture', ['id' => $facturation->getId(), 'lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }
    }

    


    #[Route('/{id}', name: 'app_logescom_vente_retour_product_show', methods: ['GET'])]
    public function show(RetourProduct $retourProduct): Response
    {
        return $this->render('logescom/vente/retour_product/show.html.twig', [
            'retour_product' => $retourProduct,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_vente_retour_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RetourProduct $retourProduct, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RetourProductType::class, $retourProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_vente_retour_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/vente/retour_product/edit.html.twig', [
            'retour_product' => $retourProduct,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_logescom_vente_retour_product_delete', methods: ['POST'])]
    public function delete(Request $request, RetourProduct $retourProduct, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$retourProduct->getId(), $request->request->get('_token'))) {
            $entityManager->remove($retourProduct);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_vente_retour_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
