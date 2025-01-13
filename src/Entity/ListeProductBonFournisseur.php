<?php

namespace App\Entity;

use App\Repository\ListeProductBonFournisseurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeProductBonFournisseurRepository::class)]
class ListeProductBonFournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductBonFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BonCommandeFournisseur $bonFournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductBonFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixAchat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBonFournisseur(): ?BonCommandeFournisseur
    {
        return $this->bonFournisseur;
    }

    public function setBonFournisseur(?BonCommandeFournisseur $bonFournisseur): static
    {
        $this->bonFournisseur = $bonFournisseur;

        return $this;
    }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrixAchat(): ?string
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?string $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }
}
