<?php

namespace App\Entity;

use App\Repository\ModifDecaissementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModifDecaissementRepository::class)]
class ModifDecaissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'modifDecaissements')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Decaissement $decaissement = null;

    #[ORM\ManyToOne(inversedBy: 'modifDecaissements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $traitePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(inversedBy: 'modifDecClients')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $client = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\ManyToOne(inversedBy: 'modifDecaissements')]
    private ?Devise $devise = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $origine = null;

    #[ORM\ManyToOne(inversedBy: 'modifDecaissements')]
    private ?Caisse $caisse = null;

    #[ORM\ManyToOne(inversedBy: 'modifDecaissements')]
    private ?Depenses $depense = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDecaissement(): ?Decaissement
    {
        return $this->decaissement;
    }

    public function setDecaissement(?Decaissement $decaissement): static
    {
        $this->decaissement = $decaissement;

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

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;

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

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(?string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDevise(): ?Devise
    {
        return $this->devise;
    }

    public function setDevise(?Devise $devise): static
    {
        $this->devise = $devise;

        return $this;
    }

    public function getOrigine(): ?string
    {
        return $this->origine;
    }

    public function setOrigine(?string $origine): static
    {
        $this->origine = $origine;

        return $this;
    }

    public function getCaisse(): ?Caisse
    {
        return $this->caisse;
    }

    public function setCaisse(?Caisse $caisse): static
    {
        $this->caisse = $caisse;

        return $this;
    }

    public function getDepense(): ?Depenses
    {
        return $this->depense;
    }

    public function setDepense(?Depenses $depense): static
    {
        $this->depense = $depense;

        return $this;
    }
}
