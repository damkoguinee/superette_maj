<?php

namespace App\Controller\Impression;

use App\Entity\Table;
use App\Entity\LieuxVentes;
use App\Entity\TableCommande;
use Doctrine\ORM\EntityManager;
use App\Service\ImpressionService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TableCommandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CommandeController extends AbstractController
{
    private $impressionService;

    public function __construct(ImpressionService $impressionService)
    {
        $this->impressionService = $impressionService;
    }

    #[Route('/valider/{lieu_vente}/{table}', name: 'app_impression_valider', methods: ['GET', 'POST'])]
    public function validerCommande(Table $table, LieuxVentes $lieu_vente, TableCommandeRepository $tableCommandeRep, EntityManagerInterface $em): Response
    {
        // Supposons que $commande contient les détails de la commande
        $commandes = $tableCommandeRep->findBy(['emplacement' => $table, 'lieuVente' => $lieu_vente, 'statut' => 'commande']);

        try {
            // Imprimer les commandes pour chaque zone
            if ($this->envoyerAuBar($commandes)) {
                $this->impressionService->imprimerCommandeBar($this->envoyerAuBar($commandes));
            }
            // Imprimer les commandes pour chaque zone
            if ($this->envoyerEnCuisine($commandes)) {
                $this->impressionService->imprimerCommandeCuisine($this->envoyerEnCuisine($commandes));
            }
            // Imprimer les commandes pour chaque zone
            if ($this->envoyerAuChicha($commandes)) {
                $this->impressionService->imprimerCommandeChicha($this->envoyerAuChicha($commandes));
            }
        } catch (\RuntimeException $e) {
            // Gérer les erreurs d'impression
            return $this->json(['message' => 'Erreur lors de l\'impression des commandes : ' . $e->getMessage()], 500);
        }

        foreach ($commandes as $commande) {

            $commande->setStatut("transmise")
                ->setPreparePar($this->getUser());
            $em->persist($commande);
            $em->flush();
        }

        // Répondre avec un message de succès
        // return $this->json(['message' => 'Commande validée avec succès']);

        $this->addFlash("success", "Commande transmise pour préparation avec succès :)");
        return new RedirectResponse($this->generateUrl('app_nhema_home_type_vente', ['lieu_vente' => $lieu_vente->getId()]));
    }

    private function envoyerEnCuisine(array $commandes): string
    {
        $formattedCommandes = [];
        $firstEmplacement = null;

        if (!empty($commandes)) {
            $firstEmplacement = $commandes[0]->getEmplacement()->getNom();
            $preparerPar = $this->getUser()->getPrenom();
            $formattedCommandes[] = "----- COMMANDE EN CUISINE -----\nTable: " . strtoupper($firstEmplacement) . "\nEnvoye par: " . strtoupper($preparerPar) . "\n";
        }

        foreach ($commandes as $key => $commande) {
            if ($commande->getService()->getId() == 1) {
                // Construire le texte formaté
                $formattedCommande = sprintf(
                    "Produit: %s\nQuantité: %s\nCommentaire: %s\n",
                    strtoupper($commande->getProduct()->getNom()),
                    $commande->getQuantite(),
                    $commande->getCommentaire()
                );
                $formattedCommandes[] = $formattedCommande;
                $formattedCommandes[] = "------------------------------\n";
            }
        }

        return implode("\n", $formattedCommandes);
    }

    private function envoyerAuBar(array $commandes): string
    {
        $formattedCommandes = [];
        $firstEmplacement = null;
    
        if (!empty($commandes)) {
            $firstEmplacement = $commandes[0]->getEmplacement()->getNom();
            $preparerPar = $this->getUser()->getPrenom();
            $formattedCommandes[] = "----- COMMANDE AU BAR -----\nTable: " . strtoupper($firstEmplacement) . "\nEnvoye par: " . strtoupper($preparerPar) . "\n";
        }
    
        foreach ($commandes as $keyb => $commande) {
            if ($commande->getService()->getId() == 2) {
                // Construire le texte formaté
                $formattedCommande = sprintf(
                    "Produit: %s\nQuantité: %s\nCommentaire: %s\n",
                    strtoupper($commande->getProduct()->getNom()),
                    $commande->getQuantite(),
                    $commande->getCommentaire()
                );
                $formattedCommandes[] = $formattedCommande;
                $formattedCommandes[] = "------------------------------\n";
            }
        }
    
        return implode("\n", $formattedCommandes);
    }

    private function envoyerAuChicha(array $commandes): string
    {
        $formattedCommandes = [];
        $firstEmplacement = null;
    
        if (!empty($commandes)) {
            $firstEmplacement = $commandes[0]->getEmplacement()->getNom();
            $preparerPar = $this->getUser()->getPrenom();
            $formattedCommandes[] = "----- COMMANDE AU CHICHA -----\nTable: " . strtoupper($firstEmplacement) . "\nEnvoye par: " . strtoupper($preparerPar) . "\n";
        }
    
        foreach ($commandes as $keyc => $commande) {
            if ($commande->getService()->getId() == 3) {
                // Construire le texte formaté
                $formattedCommande = sprintf(
                    "Produit: %s\nQuantité: %s\nCommentaire: %s\n",
                    strtoupper($commande->getProduct()->getNom()),
                    $commande->getQuantite(),
                    $commande->getCommentaire()
                );
                $formattedCommandes[] = $formattedCommande;
                $formattedCommandes[] = "------------------------------\n";
            }
        }
    
        return implode("\n", $formattedCommandes);
    }

    #[Route('/commande', name: 'app_impression_commande', methods: ['GET', 'POST'])]
    public function afficherCommande(): Response
    {
        return $this->render('impression/commande.html.twig');
    }

}