<?php

namespace App\Entity;

use App\Repository\ConfigurationLogicielRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigurationLogicielRepository::class)]
class ConfigurationLogiciel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $backgroundColor = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $livraison = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $caisse = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $formatFacture = null;

    #[ORM\Column(length: 110, nullable: true)]
    private ?string $longLogo = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $largLogo = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $nomEntreprise = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $compteClientFournisseur = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $affichageVenteCompte = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cheminSauvegarde = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cheminMysql = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $venteStock = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): static
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getLivraison(): ?string
    {
        return $this->livraison;
    }

    public function setLivraison(?string $livraison): static
    {
        $this->livraison = $livraison;

        return $this;
    }

    public function getCaisse(): ?string
    {
        return $this->caisse;
    }

    public function setCaisse(?string $caisse): static
    {
        $this->caisse = $caisse;

        return $this;
    }

    public function getFormatFacture(): ?string
    {
        return $this->formatFacture;
    }

    public function setFormatFacture(?string $formatFacture): static
    {
        $this->formatFacture = $formatFacture;

        return $this;
    }

    public function getLongLogo(): ?string
    {
        return $this->longLogo;
    }

    public function setLongLogo(?string $longLogo): static
    {
        $this->longLogo = $longLogo;

        return $this;
    }

    public function getLargLogo(): ?string
    {
        return $this->largLogo;
    }

    public function setLargLogo(?string $largLogo): static
    {
        $this->largLogo = $largLogo;

        return $this;
    }

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(?string $nomEntreprise): static
    {
        $this->nomEntreprise = $nomEntreprise;

        return $this;
    }

    public function getCompteClientFournisseur(): ?string
    {
        return $this->compteClientFournisseur;
    }

    public function setCompteClientFournisseur(?string $compteClientFournisseur): static
    {
        $this->compteClientFournisseur = $compteClientFournisseur;

        return $this;
    }

    public function getAffichageVenteCompte(): ?string
    {
        return $this->affichageVenteCompte;
    }

    public function setAffichageVenteCompte(?string $affichageVenteCompte): static
    {
        $this->affichageVenteCompte = $affichageVenteCompte;

        return $this;
    }

    public function getCheminSauvegarde(): ?string
    {
        return $this->cheminSauvegarde;
    }

    public function setCheminSauvegarde(?string $cheminSauvegarde): static
    {
        $this->cheminSauvegarde = $cheminSauvegarde;

        return $this;
    }

    public function getCheminMysql(): ?string
    {
        return $this->cheminMysql;
    }

    public function setCheminMysql(?string $cheminMysql): static
    {
        $this->cheminMysql = $cheminMysql;

        return $this;
    }

    public function getVenteStock(): ?string
    {
        return $this->venteStock;
    }

    public function setVenteStock(?string $venteStock): static
    {
        $this->venteStock = $venteStock;

        return $this;
    }
}
