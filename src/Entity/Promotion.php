<?php

namespace App\Entity;

use App\Repository\PromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Products::class, inversedBy: 'promotions')]
    private Collection $produits;

    #[ORM\Column(nullable: true)]
    private ?float $quantiteMin = null;

   

    #[ORM\ManyToOne(inversedBy: 'promotions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Products $produitBonus = null;

    #[ORM\Column]
    private ?float $quantiteBonus = null;

    #[ORM\ManyToOne(inversedBy: 'promotions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\Column(length: 10)]
    private ?string $etat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'promotions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Products>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Products $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
        }

        return $this;
    }

    public function removeProduit(Products $produit): static
    {
        $this->produits->removeElement($produit);

        return $this;
    }

    public function getQuantiteMin(): ?float
    {
        return $this->quantiteMin;
    }

    public function setQuantiteMin(float $quantiteMin): static
    {
        $this->quantiteMin = $quantiteMin;

        return $this;
    }

    public function getProduitBonus(): ?Products
    {
        return $this->produitBonus;
    }

    public function setProduitBonus(?Products $produitBonus): static
    {
        $this->produitBonus = $produitBonus;

        return $this;
    }

    public function getQuantiteBonus(): ?float
    {
        return $this->quantiteBonus;
    }

    public function setQuantiteBonus(float $quantiteBonus): static
    {
        $this->quantiteBonus = $quantiteBonus;

        return $this;
    }

    public function getLieuVente(): ?LieuxVentes
    {
        return $this->lieuVente;
    }

    public function setLieuVente(?LieuxVentes $lieuVente): static
    {
        $this->lieuVente = $lieuVente;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDateSaisie(): ?\DateTimeInterface
    {
        return $this->dateSaisie;
    }

    public function setDateSaisie(\DateTimeInterface $dateSaisie): static
    {
        $this->dateSaisie = $dateSaisie;

        return $this;
    }

    public function getSaisiePar(): ?User
    {
        return $this->saisiePar;
    }

    public function setSaisiePar(?User $saisiePar): static
    {
        $this->saisiePar = $saisiePar;

        return $this;
    }

    
}
