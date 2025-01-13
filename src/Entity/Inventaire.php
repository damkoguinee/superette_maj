<?php

namespace App\Entity;

use App\Repository\InventaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventaireRepository::class)]
class Inventaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeInventaire $inventaire = null;

    #[ORM\ManyToOne(inversedBy: 'inventaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantiteInit = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantiteInv = null;

    #[ORM\Column(nullable: true)]
    private ?float $ecart = null;

    #[ORM\ManyToOne(inversedBy: 'inventaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeStock $stock = null;

    #[ORM\ManyToOne(inversedBy: 'inventaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 15)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInventaire = null;

    #[ORM\OneToMany(mappedBy: 'inventaire', targetEntity: MouvementProduct::class, orphanRemoval:
    true, cascade:['remove'])]
    private Collection $mouvementProducts;

    #[ORM\OneToMany(mappedBy: 'inventaire', targetEntity: AnomalieProduit::class, orphanRemoval:
    true, cascade:['remove'])]
    private Collection $anomalieProduits;

    public function __construct()
    {
        $this->mouvementProducts = new ArrayCollection();
        $this->anomalieProduits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventaire(): ?ListeInventaire
    {
        return $this->inventaire;
    }

    public function setInventaire(?ListeInventaire $inventaire): static
    {
        $this->inventaire = $inventaire;

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

    public function getQuantiteInit(): ?float
    {
        return $this->quantiteInit;
    }

    public function setQuantiteInit(?float $quantiteInit): static
    {
        $this->quantiteInit = $quantiteInit;

        return $this;
    }

    public function getQuantiteInv(): ?float
    {
        return $this->quantiteInv;
    }

    public function setQuantiteInv(?float $quantiteInv): static
    {
        $this->quantiteInv = $quantiteInv;

        return $this;
    }

    public function getEcart(): ?float
    {
        return $this->ecart;
    }

    public function setEcart(?float $ecart): static
    {
        $this->ecart = $ecart;

        return $this;
    }

    public function getStock(): ?ListeStock
    {
        return $this->stock;
    }

    public function setStock(?ListeStock $stock): static
    {
        $this->stock = $stock;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateInventaire(): ?\DateTimeInterface
    {
        return $this->dateInventaire;
    }

    public function setDateInventaire(\DateTimeInterface $dateInventaire): static
    {
        $this->dateInventaire = $dateInventaire;

        return $this;
    }

    /**
     * @return Collection<int, MouvementProduct>
     */
    public function getMouvementProducts(): Collection
    {
        return $this->mouvementProducts;
    }

    public function addMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if (!$this->mouvementProducts->contains($mouvementProduct)) {
            $this->mouvementProducts->add($mouvementProduct);
            $mouvementProduct->setInventaire($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getInventaire() === $this) {
                $mouvementProduct->setInventaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AnomalieProduit>
     */
    public function getAnomalieProduits(): Collection
    {
        return $this->anomalieProduits;
    }

    public function addAnomalieProduit(AnomalieProduit $anomalieProduit): static
    {
        if (!$this->anomalieProduits->contains($anomalieProduit)) {
            $this->anomalieProduits->add($anomalieProduit);
            $anomalieProduit->setInventaire($this);
        }

        return $this;
    }

    public function removeAnomalieProduit(AnomalieProduit $anomalieProduit): static
    {
        if ($this->anomalieProduits->removeElement($anomalieProduit)) {
            // set the owning side to null (unless already changed)
            if ($anomalieProduit->getInventaire() === $this) {
                $anomalieProduit->setInventaire(null);
            }
        }

        return $this;
    }
}
