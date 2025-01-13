<?php

namespace App\Entity;

use App\Repository\MouvementProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MouvementProductRepository::class)]
class MouvementProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeStock $stockProduct = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOperation = null;

    #[ORM\Column(length: 100)]
    private ?string $origine = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProductsClient')]
    private ?User $client = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    private ?Inventaire $inventaire = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    private ?AnomalieProduit $anomalie = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    private ?ListeTransfertProduct $transfert = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    private ?ListeProductAchatFournisseur $achatFournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    private ?Livraison $livraison = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    private ?ListeProductAchatFournisseurMultiple $livraisonMultiple = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementProducts')]
    private ?SortieStock $sortieStock = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStockProduct(): ?ListeStock
    {
        return $this->stockProduct;
    }

    public function setStockProduct(?ListeStock $stockProduct): static
    {
        $this->stockProduct = $stockProduct;

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

    public function setQuantite(?float $quantite): static
    {
        $this->quantite = $quantite;

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

    public function getPersonnel(): ?User
    {
        return $this->personnel;
    }

    public function setPersonnel(?User $personnel): static
    {
        $this->personnel = $personnel;

        return $this;
    }

    public function getDateOperation(): ?\DateTimeInterface
    {
        return $this->dateOperation;
    }

    public function setDateOperation(\DateTimeInterface $dateOperation): static
    {
        $this->dateOperation = $dateOperation;

        return $this;
    }

    public function getOrigine(): ?string
    {
        return $this->origine;
    }

    public function setOrigine(string $origine): static
    {
        $this->origine = $origine;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getInventaire(): ?Inventaire
    {
        return $this->inventaire;
    }

    public function setInventaire(?Inventaire $inventaire): static
    {
        $this->inventaire = $inventaire;

        return $this;
    }

    public function getAnomalie(): ?AnomalieProduit
    {
        return $this->anomalie;
    }

    public function setAnomalie(?AnomalieProduit $anomalie): static
    {
        $this->anomalie = $anomalie;

        return $this;
    }

    public function getTransfert(): ?ListeTransfertProduct
    {
        return $this->transfert;
    }

    public function setTransfert(?ListeTransfertProduct $transfert): static
    {
        $this->transfert = $transfert;

        return $this;
    }

    public function getAchatFournisseur(): ?ListeProductAchatFournisseur
    {
        return $this->achatFournisseur;
    }

    public function setAchatFournisseur(?ListeProductAchatFournisseur $achatFournisseur): static
    {
        $this->achatFournisseur = $achatFournisseur;

        return $this;
    }

    public function getLivraison(): ?Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?Livraison $livraison): static
    {
        $this->livraison = $livraison;

        return $this;
    }

    public function getLivraisonMultiple(): ?ListeProductAchatFournisseurMultiple
    {
        return $this->livraisonMultiple;
    }

    public function setLivraisonMultiple(?ListeProductAchatFournisseurMultiple $livraisonMultiple): static
    {
        $this->livraisonMultiple = $livraisonMultiple;

        return $this;
    }

    public function getSortieStock(): ?SortieStock
    {
        return $this->sortieStock;
    }

    public function setSortieStock(?SortieStock $sortieStock): static
    {
        $this->sortieStock = $sortieStock;

        return $this;
    }
}
