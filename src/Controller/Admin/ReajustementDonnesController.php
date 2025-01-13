<?php

namespace App\Controller\Admin;

use App\Entity\CommandeProduct;
use App\Repository\CommandeProductRepository;
use App\Repository\DecaissementRepository;
use App\Repository\DepensesRepository;
use App\Repository\UserRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\FacturationRepository;
use App\Repository\ModePaiementRepository;
use App\Repository\MouvementCaisseRepository;
use App\Repository\MouvementCollaborateurRepository;
use App\Repository\MouvementProductRepository;
use App\Repository\ProductsRepository;
use App\Repository\StockRepository;
use App\Repository\VersementRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/reajustement/donnees')]
class ReajustementDonnesController extends AbstractController
{
    #[Route('/maj/prix', name: 'app_admin_reajustement_donnees_maj_prix')]
    public function majPrixRevient(StockRepository $stockRep, CommandeProductRepository $commandeProductRep, EntityManagerInterface $em): Response
    {
        $stocks = $stockRep->findAll();
        foreach ($stocks as $value) {
            $commandes = $commandeProductRep->findBy(['product' => $value->getProducts()]);
            foreach ($commandes as $cmd) {
                // $difference = $cmd->getPrixVente() - $cmd->getPrixRevient();
                // if ($difference <= 0) {
                //     $cmd->setPrixRevient($value->getPrixRevient());
                // }
                if (empty($cmd->getPrixRevient())) {
                    $cmd->setPrixRevient($value->getPrixRevient());
                }
                if (abs($cmd->getPrixRevient()) > (100 * $cmd->getPrixVente())) {
                    $cmd->setPrixRevient($value->getPrixRevient());

                }
                // $cmd->setPrixRevient($value->getPrixRevient());

                $em->persist($cmd);
            }
        }
        $em->flush();
        return $this->redirectToRoute('app_logescom_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/maj/livraison', name: 'app_admin_reajustement_donnees_maj_livraison')]
    public function majLivraison(FacturationRepository $facturationRep, CommandeProductRepository $commandeProductRep, EntityManagerInterface $em): Response
    {
        $commandes = $commandeProductRep->findAll();
        foreach ($commandes as $value) {
            $livre = $value->getQuantiteLivre() ?: 0;
            $resteLivraison = $value->getQuantite() - $livre;
            if ($resteLivraison == 0 ) {
                $facturation = $value->getFacturation();
                $facturation->setEtatLivraison('livré');
                $em->persist($facturation);
            }
            
        }
        $em->flush();
        return $this->redirectToRoute('app_logescom_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/maj/accent', name: 'app_admin_reajustement_donnees_maj_accent')]
    public function MajAccentCollaborateur(UserRepository $userRep, ProductsRepository $productsRep, EntityManagerInterface $em, EntrepriseRepository $entrepriseRep): Response
    {       
        $maj_client = $userRep->findAll();
        foreach ($maj_client as $client) {
            $client->setNom(str_replace('&eacute;', 'é', $client->getNom()));
            $client->setPrenom(str_replace('&eacute;', 'é', $client->getPrenom()));
            $em->persist($client);
        }

        $maj_product = $productsRep->findAll();
        foreach ($maj_product as $product) {
            $product->setDesignation(str_replace('&eacute;', 'é', $product->getDesignation()));
            $product->setDesignation(str_replace('&egrave;', 'è', $product->getDesignation()));
            $product->setDesignation(str_replace('&agrave;', 'à', $product->getDesignation()));
            $product->setDesignation(str_replace('&ecirc;', 'ê', $product->getDesignation()));
            $product->setDesignation(str_replace('&ccedil;', 'ç', $product->getDesignation()));
            $product->setDesignation(str_replace('&ocirc;', 'ô', $product->getDesignation()));
            $product->setDesignation(str_replace('&times;', 'x', $product->getDesignation()));
            $em->persist($product);
        }
        $em->flush();

        return $this->redirectToRoute('app_logescom_home', [], Response::HTTP_SEE_OTHER);

    }

    #[Route('/maj/caisse/modepaie', name: 'app_admin_reajustement_donnees_maj_caisse_modepaie')]
    public function MajCaisseModePaie(MouvementCaisseRepository $mouvementCaisseRepository, ModePaiementRepository $modePaieRep, EntityManagerInterface $em, EntrepriseRepository $entrepriseRep): Response
    {       
        $maj_caisse = $mouvementCaisseRepository->findBy(['modePaie' => Null]);
        foreach ($maj_caisse as $value) {
            $value->setModePaie($value->getModePaie() ? $value->getModePaie() : $modePaieRep->find(1));
            $em->persist($value);
        }
        $em->flush();

        return $this->redirectToRoute('app_logescom_home', [], Response::HTTP_SEE_OTHER);

    }

    #[Route('/maj/anomalie/facturation/paye', name: 'app_admin_reajustement_donnees_maj_caisse_modepaie')]
    public function MajAnomalieFacturation(FacturationRepository $facturationRep, MouvementCollaborateurRepository $mouvementCollabRep, EntityManagerInterface $em, EntrepriseRepository $entrepriseRep): Response
    { 
        $facturations = $facturationRep->findBy(['etat' => 'payé'], ['client' => 'ASC']) ;
        $anomalies = [];
        foreach ($facturations as $key => $facture) {
            $mouvement = $mouvementCollabRep->findOneBy(['facture' => $facture, 'origine' => 'facturation']);
            // dd($mouvement);
            if ($mouvement && $mouvement->getMontant() != 0) {
                $anomalies[] = [
                    'facture' => $facture,
                    'mouvement' => $mouvement
                ];
            }
        }

        return $this->render('logescom/vente/facturation/anomalie_vente_paye.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'anomalies' => $anomalies,
            
        ]);      


    }

    #[Route('/maj/datevente', name: 'app_admin_reajustement_donnees_maj_date_vente')]
    public function majDateVente(MouvementCaisseRepository $mouvementCaisseRep, DecaissementRepository $decaissementRep, DepensesRepository $depenseRep, VersementRepository $versementRep, EntityManagerInterface $em): Response
    {
        $mouvements = $mouvementCaisseRep->findAll();

        foreach ($mouvements as $mouvement) {
            $dateOperation = $mouvement->getDateOperation();
            $dateSaisie = $mouvement->getDateSaisie();

            // Vérifier si l'heure de dateOperation est 00:00:00
            if ($dateOperation && $dateOperation->format('H:i:s') === '00:00:00') {
                // Remplacer l'heure de dateOperation par celle de dateSaisie
                $heure_date_saisie = $dateSaisie->format('H:i:s');

                $dateAjustee = new DateTime($dateOperation->format('Y-m-d').' '.$heure_date_saisie);

                $mouvement->setDateOperation($dateAjustee);

                // Persister les changements dans la base de données
                $em->persist($mouvement);
            }
        }

        $decaissements = $decaissementRep->findAll();

        foreach ($decaissements as $decaissement) {
            $dateOperation = $decaissement->getDateDecaissement();
            $dateSaisie = $decaissement->getDateSaisie();

            // Vérifier si l'heure de dateOperation est 00:00:00
            if ($dateOperation && $dateOperation->format('H:i:s') === '00:00:00') {
                // Remplacer l'heure de dateOperation par celle de dateSaisie
                $heure_date_saisie = $dateSaisie->format('H:i:s');

                $dateAjustee = new DateTime($dateOperation->format('Y-m-d').' '.$heure_date_saisie);

                $decaissement->setDateDecaissement($dateAjustee);

                // Persister les changements dans la base de données
                $em->persist($decaissement);
            }
        }

        $depenses = $depenseRep->findAll();

        foreach ($depenses as $depense) {
            $dateOperation = $depense->getDateDepense();
            $dateSaisie = $depense->getDateSaisie();

            // Vérifier si l'heure de dateOperation est 00:00:00
            if ($dateOperation && $dateOperation->format('H:i:s') === '00:00:00') {
                // Remplacer l'heure de dateOperation par celle de dateSaisie
                $heure_date_saisie = $dateSaisie->format('H:i:s');

                $dateAjustee = new DateTime($dateOperation->format('Y-m-d').' '.$heure_date_saisie);

                $depense->setDateDepense($dateAjustee);

                // Persister les changements dans la base de données
                $em->persist($depense);
            }
        }

        $versements = $versementRep->findAll();

        foreach ($versements as $versement) {
            $dateOperation = $versement->getDateVersement();
            $dateSaisie = $versement->getDateSaisie();

            // Vérifier si l'heure de dateOperation est 00:00:00
            if ($dateOperation && $dateOperation->format('H:i:s') === '00:00:00') {
                // Remplacer l'heure de dateOperation par celle de dateSaisie
                $heure_date_saisie = $dateSaisie->format('H:i:s');

                $dateAjustee = new DateTime($dateOperation->format('Y-m-d').' '.$heure_date_saisie);

                $versement->setDateVersement($dateAjustee);

                // Persister les changements dans la base de données
                $em->persist($versement);
            }
        }

        $em->flush();
        return $this->redirectToRoute('app_logescom_home', [], Response::HTTP_SEE_OTHER);
    }
}
