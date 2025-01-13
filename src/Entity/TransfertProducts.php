<?php

namespace App\Entity;

use App\Repository\TransfertProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransfertProductsRepository::class)]
class TransfertProducts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transfertProduct')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVenteDepart = null;

    #[ORM\ManyToOne(inversedBy: 'transfertProductRecep')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVenteRecep = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'transfertProduct')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creePar = null;

    #[ORM\ManyToOne(inversedBy: 'transfertProduct')]
    private ?User $traitePar = null;

    #[ORM\Column(length: 50)]
    private ?string $etat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnvoi = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReception = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $chargesTransfert = null;

    #[ORM\OneToMany(mappedBy: 'transfert', targetEntity: ListeTransfertProduct::class, orphanRemoval: true, cascade:['persist'])]
    private Collection $listeTransfertProducts;

    public function __construct()
    {
        $this->listeTransfertProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLieuVenteDepart(): ?LieuxVentes
    {
        return $this->lieuVenteDepart;
    }

    public function setLieuVenteDepart(?LieuxVentes $lieuVenteDepart): static
    {
        $this->lieuVenteDepart = $lieuVenteDepart;

        return $this;
    }

    public function getLieuVenteRecep(): ?LieuxVentes
    {
        return $this->lieuVenteRecep;
    }

    public function setLieuVenteRecep(?LieuxVentes $lieuVenteRecep): static
    {
        $this->lieuVenteRecep = $lieuVenteRecep;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreePar(): ?User
    {
        return $this->creePar;
    }

    public function setCreePar(?User $creePar): static
    {
        $this->creePar = $creePar;

        return $this;
    }

    public function getTraitePar(): ?User
    {
        return $this->traitePar;
    }

    public function setTraitePar(?User $traitePar): static
    {
        $this->traitePar = $traitePar;

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

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $dateEnvoi): static
    {
        $this->dateEnvoi = $dateEnvoi;

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

    public function getChargesTransfert(): ?string
    {
        return $this->chargesTransfert;
    }

    public function setChargesTransfert(?string $chargesTransfert): static
    {
        $this->chargesTransfert = $chargesTransfert;

        return $this;
    }

    /**
     * @return Collection<int, ListeTransfertProduct>
     */
    public function getListeTransfertProducts(): Collection
    {
        return $this->listeTransfertProducts;
    }

    public function addListeTransfertProduct(ListeTransfertProduct $listeTransfertProduct): static
    {
        if (!$this->listeTransfertProducts->contains($listeTransfertProduct)) {
            $this->listeTransfertProducts->add($listeTransfertProduct);
            $listeTransfertProduct->setTransfert($this);
        }

        return $this;
    }

    public function removeListeTransfertProduct(ListeTransfertProduct $listeTransfertProduct): static
    {
        if ($this->listeTransfertProducts->removeElement($listeTransfertProduct)) {
            // set the owning side to null (unless already changed)
            if ($listeTransfertProduct->getTransfert() === $this) {
                $listeTransfertProduct->setTransfert(null);
            }
        }

        return $this;
    }
}
