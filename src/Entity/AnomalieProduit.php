<?php

namespace App\Entity;

use App\Repository\AnomalieProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnomalieProduitRepository::class)]
class AnomalieProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'anomalieProduits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeStock $stock = null;

    #[ORM\ManyToOne(inversedBy: 'anomalieProduits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $prixRevient = null;

    #[ORM\ManyToOne(inversedBy: 'anomalieProduits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\ManyToOne(inversedBy: 'anomalieProduits')]
    private ?Inventaire $inventaire = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAnomalie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comentaire = null;

    #[ORM\OneToMany(mappedBy: 'anomalie', targetEntity: MouvementProduct::class, orphanRemoval: true, cascade: ['remove'])]
    private Collection $mouvementProducts;

    public function __construct()
    {
        $this->mouvementProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrixRevient(): ?string
    {
        return $this->prixRevient;
    }

    public function setPrixRevient(?string $prixRevient): static
    {
        $this->prixRevient = $prixRevient;

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

    public function getInventaire(): ?Inventaire
    {
        return $this->inventaire;
    }

    public function setInventaire(?Inventaire $inventaire): static
    {
        $this->inventaire = $inventaire;

        return $this;
    }

    public function getDateAnomalie(): ?\DateTimeInterface
    {
        return $this->dateAnomalie;
    }

    public function setDateAnomalie(\DateTimeInterface $dateAnomalie): static
    {
        $this->dateAnomalie = $dateAnomalie;

        return $this;
    }

    public function getComentaire(): ?string
    {
        return $this->comentaire;
    }

    public function setComentaire(?string $comentaire): static
    {
        $this->comentaire = $comentaire;

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
            $mouvementProduct->setAnomalie($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getAnomalie() === $this) {
                $mouvementProduct->setAnomalie(null);
            }
        }

        return $this;
    }
}
