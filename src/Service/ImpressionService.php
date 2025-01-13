<?php

namespace App\Service;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class ImpressionService
{
    private $connecteurs;

    public function __construct()
    {
        // Initialiser les connecteurs dans un tableau pour faciliter la gestion
        $this->connecteurs = [
            'bar' => new WindowsPrintConnector("smb://DAMKO/XP-80C"),
            'cuisine' => new WindowsPrintConnector("smb://DAMKO/XP-80C"),
            'chicha' => new WindowsPrintConnector("smb://DAMKO/XP-80C-premax")
        ];
    }

    private function imprimer(string $zone, string $texte): void
    {
        try {
            $printer = new Printer($this->connecteurs[$zone]);
            $printer->text($texte);
            $printer->cut();
            $printer->close();
        } catch (\Exception $e) {
            // Gestion des erreurs d'impression
            error_log("Erreur d'impression pour la zone $zone : " . $e->getMessage());
            throw new \RuntimeException("Erreur d'impression pour la zone $zone : " . $e->getMessage());
        }
    }

    public function imprimerCommandeBar(string $texte): void
    {
        error_log("Impression de la commande pour le bar : $texte");
        $this->imprimer('bar', $texte);
    }

    public function imprimerCommandeCuisine(string $texte): void
    {
        error_log("Impression de la commande pour la cuisine : $texte");
        $this->imprimer('cuisine', $texte);
    }

    public function imprimerCommandeChicha(string $texte): void
    {
        error_log("Impression de la commande pour la chicha : $texte");
        $this->imprimer('chicha', $texte);
    }
}