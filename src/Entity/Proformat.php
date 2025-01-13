<?php

namespace App\Entity;

use App\Repository\ProformatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ProformatRepository::class)]
class Proformat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $numeroProformat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $totalProformat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'proformats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne(inversedBy: 'proformats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\ManyToOne(inversedBy: 'proformats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\OneToMany(mappedBy: 'proformat', targetEntity: ProformatProduct::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $proformatProducts;

    #[ORM\OneToMany(mappedBy: 'proformat', targetEntity: Facturation::class)]
    private Collection $facturations;

    #[ORM\OneToMany(mappedBy: 'proformat', targetEntity: ProformatFraisSup::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $proformatFraisSups;

    public function __construct()
    {
        $this->proformatProducts = new ArrayCollection();
        $this->facturations = new ArrayCollection();
        $this->proformatFraisSups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroProformat(): ?string
    {
        return $this->numeroProformat;
    }

    public function setNumeroProformat(string $numeroProformat): static
    {
        $this->numeroProformat = $numeroProformat;

        return $this;
    }

    public function getTotalProformat(): ?string
    {
        return $this->totalProformat;
    }

    public function setTotalProformat(?string $totalProformat): static
    {
        $this->totalProformat = $totalProformat;

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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

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

    public function getSaisiePar(): ?User
    {
        return $this->saisiePar;
    }

    public function setSaisiePar(?User $saisiePar): static
    {
        $this->saisiePar = $saisiePar;

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

    /**
     * @return Collection<int, ProformatProduct>
     */
    public function getProformatProducts(): Collection
    {
        return $this->proformatProducts;
    }

    public function addProformatProduct(ProformatProduct $proformatProduct): static
    {
        if (!$this->proformatProducts->contains($proformatProduct)) {
            $this->proformatProducts->add($proformatProduct);
            $proformatProduct->setProformat($this);
        }

        return $this;
    }

    public function removeProformatProduct(ProformatProduct $proformatProduct): static
    {
        if ($this->proformatProducts->removeElement($proformatProduct)) {
            // set the owning side to null (unless already changed)
            if ($proformatProduct->getProformat() === $this) {
                $proformatProduct->setProformat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Facturation>
     */
    public function getFacturations(): Collection
    {
        return $this->facturations;
    }

    public function addFacturation(Facturation $facturation): static
    {
        if (!$this->facturations->contains($facturation)) {
            $this->facturations->add($facturation);
            $facturation->setProformat($this);
        }

        return $this;
    }

    public function removeFacturation(Facturation $facturation): static
    {
        if ($this->facturations->removeElement($facturation)) {
            // set the owning side to null (unless already changed)
            if ($facturation->getProformat() === $this) {
                $facturation->setProformat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProformatFraisSup>
     */
    public function getProformatFraisSups(): Collection
    {
        return $this->proformatFraisSups;
    }

    public function addProformatFraisSup(ProformatFraisSup $proformatFraisSup): static
    {
        if (!$this->proformatFraisSups->contains($proformatFraisSup)) {
            $this->proformatFraisSups->add($proformatFraisSup);
            $proformatFraisSup->setProformat($this);
        }

        return $this;
    }

    public function removeProformatFraisSup(ProformatFraisSup $proformatFraisSup): static
    {
        if ($this->proformatFraisSups->removeElement($proformatFraisSup)) {
            // set the owning side to null (unless already changed)
            if ($proformatFraisSup->getProformat() === $this) {
                $proformatFraisSup->setProformat(null);
            }
        }

        return $this;
    }
}
