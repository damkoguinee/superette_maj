<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stockProduit')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeStock $stockProduit = null;

    #[ORM\ManyToOne(inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantite = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $prixAchat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $prixRevient = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $prixVente = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePeremption = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProducts(): ?Products
    {
        return $this->product;
    }

    public function setProducts(?Products $product): static
    {
        $this->product = $product;

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

    public function getPrixVente(): ?string
    {
        return $this->prixVente;
    }

    public function setPrixVente(?string $prixVente): static
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    public function getDatePeremption(): ?\DateTimeInterface
    {
        return $this->datePeremption;
    }

    public function setDatePeremption(?\DateTimeInterface $datePeremption): static
    {
        $this->datePeremption = $datePeremption;

        return $this;
    }

    public function getStockProduit(): ?ListeStock
    {
        return $this->stockProduit;
    }

    public function setStockProduit(?ListeStock $stockProduit): static
    {
        $this->stockProduit = $stockProduit;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(?float $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrixRevient(): ?string
    {
        return $this->prixRevient;
    }

    public function setPrixRevient(?string $prixRevient): static
    {
        $this->prixRevient = $prixRevient;

        return $this;
    }

    
}
