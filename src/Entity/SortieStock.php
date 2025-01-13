<?php

namespace App\Entity;

use App\Repository\SortieStockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SortieStockRepository::class)]
class SortieStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sortieStocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\ManyToOne(inversedBy: 'sortieStocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeStock $stock = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'sortieStocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOperation = null;

    #[ORM\OneToMany(mappedBy: 'sortieStock', targetEntity: MouvementProduct::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementProducts;

    public function __construct()
    {
        $this->mouvementProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStock(): ?ListeStock
    {
        return $this->stock;
    }

    public function setStock(?ListeStock $stock): static
    {
        $this->stock = $stock;

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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

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

    public function getDateSaisie(): ?\DateTimeInterface
    {
        return $this->dateSaisie;
    }

    public function setDateSaisie(\DateTimeInterface $dateSaisie): static
    {
        $this->dateSaisie = $dateSaisie;

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
            $mouvementProduct->setSortieStock($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getSortieStock() === $this) {
                $mouvementProduct->setSortieStock(null);
            }
        }

        return $this;
    }
}
