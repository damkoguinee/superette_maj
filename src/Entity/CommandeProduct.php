<?php

namespace App\Entity;

use App\Repository\CommandeProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeProductRepository::class)]
class CommandeProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandeProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Facturation $facturation = null;

    #[ORM\ManyToOne(inversedBy: 'commandeProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixVente = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixRevient = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixAchat = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantiteLivre = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Livraison::class, cascade:['remove'])]
    private Collection $livraisons;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $remise = null;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: RetourProduct::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $retourProducts;

    public function __construct()
    {
        $this->livraisons = new ArrayCollection();
        $this->retourProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacturation(): ?Facturation
    {
        return $this->facturation;
    }

    public function setFacturation(?Facturation $facturation): static
    {
        $this->facturation = $facturation;

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

    public function getPrixRevient(): ?string
    {
        return $this->prixRevient;
    }

    public function setPrixRevient(?string $prixRevient): static
    {
        $this->prixRevient = $prixRevient;

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

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(?float $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getQuantiteLivre(): ?float
    {
        return $this->quantiteLivre;
    }

    public function setQuantiteLivre(?float $quantiteLivre): static
    {
        $this->quantiteLivre = $quantiteLivre;

        return $this;
    }

    /**
     * @return Collection<int, Livraison>
     */
    public function getLivraisons(): Collection
    {
        return $this->livraisons;
    }

    public function addLivraison(Livraison $livraison): static
    {
        if (!$this->livraisons->contains($livraison)) {
            $this->livraisons->add($livraison);
            $livraison->setCommande($this);
        }

        return $this;
    }

    public function removeLivraison(Livraison $livraison): static
    {
        if ($this->livraisons->removeElement($livraison)) {
            // set the owning side to null (unless already changed)
            if ($livraison->getCommande() === $this) {
                $livraison->setCommande(null);
            }
        }

        return $this;
    }

    public function getRemise(): ?string
    {
        return $this->remise;
    }

    public function setRemise(?string $remise): static
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

    /**
     * @return Collection<int, RetourProduct>
     */
    public function getRetourProducts(): Collection
    {
        return $this->retourProducts;
    }

    public function addRetourProduct(RetourProduct $retourProduct): static
    {
        if (!$this->retourProducts->contains($retourProduct)) {
            $this->retourProducts->add($retourProduct);
            $retourProduct->setCommande($this);
        }

        return $this;
    }

    public function removeRetourProduct(RetourProduct $retourProduct): static
    {
        if ($this->retourProducts->removeElement($retourProduct)) {
            // set the owning side to null (unless already changed)
            if ($retourProduct->getCommande() === $this) {
                $retourProduct->setCommande(null);
            }
        }

        return $this;
    }
}
