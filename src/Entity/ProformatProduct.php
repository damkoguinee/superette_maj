<?php

namespace App\Entity;

use App\Repository\ProformatProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProformatProductRepository::class)]
class ProformatProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'proformatProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Proformat $proformat = null;

    #[ORM\ManyToOne(inversedBy: 'proformatProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixVente = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?float $remise = null;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProformat(): ?Proformat
    {
        return $this->proformat;
    }

    public function setProformat(?Proformat $proformat): static
    {
        $this->proformat = $proformat;

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

    public function getPrixVente(): ?string
    {
        return $this->prixVente;
    }

    public function setPrixVente(?string $prixVente): static
    {
        $this->prixVente = $prixVente;

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

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(?float $remise): static
    {
        $this->remise = $remise;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(?float $tva): static
    {
        $this->tva = $tva;

        return $this;
    }
}
