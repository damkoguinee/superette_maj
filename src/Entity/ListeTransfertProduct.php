<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Entity\MouvementProduct;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\ListeTransfertProductRepository;

#[ORM\Entity(repositoryClass: ListeTransfertProductRepository::class)]
class ListeTransfertProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listeTransfertProduct')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\ManyToOne(inversedBy: 'listeTransfertProductDepart')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeStock $stockDepart = null;

    #[ORM\ManyToOne(inversedBy: 'listeTransfertProductRecep')]
    private ?ListeStock $stockRecep = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantiteRecep = null;

    #[ORM\ManyToOne(inversedBy: 'listeTransfertProduct')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comentaire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTransfert = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReception = null;

    #[ORM\OneToMany(mappedBy: 'transfert', targetEntity: MouvementProduct::class, orphanRemoval: true, cascade:['persist'])]
    private Collection $mouvementProducts;

    #[ORM\ManyToOne(inversedBy: 'listeTransfertProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TransfertProducts $transfert = null;

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

    public function getStockDepart(): ?ListeStock
    {
        return $this->stockDepart;
    }

    public function setStockDepart(?ListeStock $stockDepart): static
    {
        $this->stockDepart = $stockDepart;

        return $this;
    }

    public function getStockRecep(): ?ListeStock
    {
        return $this->stockRecep;
    }

    public function setStockRecep(?ListeStock $stockRecep): static
    {
        $this->stockRecep = $stockRecep;

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

    public function getPersonnel(): ?User
    {
        return $this->personnel;
    }

    public function setPersonnel(?User $personnel): static
    {
        $this->personnel = $personnel;

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

    public function getDateTransfert(): ?\DateTimeInterface
    {
        return $this->dateTransfert;
    }

    public function setDateTransfert(\DateTimeInterface $dateTransfert): static
    {
        $this->dateTransfert = $dateTransfert;

        return $this;
    }

    public function getDateReception(): ?\DateTimeInterface
    {
        return $this->dateReception;
    }

    public function setDateReception(?\DateTimeInterface $dateReception): static
    {
        $this->dateReception = $dateReception;

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
            $mouvementProduct->setTransfert($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getTransfert() === $this) {
                $mouvementProduct->setTransfert(null);
            }
        }

        return $this;
    }

    public function getTransfert(): ?TransfertProducts
    {
        return $this->transfert;
    }

    public function setTransfert(?TransfertProducts $transfert): static
    {
        $this->transfert = $transfert;

        return $this;
    }

    public function getQuantiteRecep(): ?float
    {
        return $this->quantiteRecep;
    }

    public function setQuantiteRecep(?float $quantiteRecep): static
    {
        $this->quantiteRecep = $quantiteRecep;

        return $this;
    }
}
