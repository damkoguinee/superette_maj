<?php

namespace App\Entity;

use App\Repository\ModificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModificationRepository::class)]
class Modification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\Column(length: 50)]
    private ?string $origine = null;

    #[ORM\ManyToOne(inversedBy: 'modifications')]
    private ?User $collaborateur = null;

    #[ORM\ManyToOne(inversedBy: 'modifications')]
    private ?Devise $devise = null;

    #[ORM\ManyToOne(inversedBy: 'modifications')]
    private ?Caisse $caisse = null;

    #[ORM\ManyToOne(inversedBy: 'modifications')]
    private ?User $traitePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOperation = null;

    #[ORM\ManyToOne(inversedBy: 'modifications')]
    private ?Decaissement $decaissement = null;

    #[ORM\ManyToOne(inversedBy: 'modifications')]
    private ?Depenses $depense = null;

    #[ORM\ManyToOne(inversedBy: 'modifications')]
    private ?Versement $versement = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrigine(): ?string
    {
        return $this->origine;
    }

    public function setOrigine(string $origine): static
    {
        $this->origine = $origine;

        return $this;
    }

    public function getCollaborateur(): ?User
    {
        return $this->collaborateur;
    }

    public function setCollaborateur(?User $collaborateur): static
    {
        $this->collaborateur = $collaborateur;

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

    public function getCaisse(): ?Caisse
    {
        return $this->caisse;
    }

    public function setCaisse(?Caisse $caisse): static
    {
        $this->caisse = $caisse;

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

    public function getDecaissement(): ?Decaissement
    {
        return $this->decaissement;
    }

    public function setDecaissement(?Decaissement $decaissement): static
    {
        $this->decaissement = $decaissement;

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

    public function getVersement(): ?Versement
    {
        return $this->versement;
    }

    public function setVersement(?Versement $versement): static
    {
        $this->versement = $versement;

        return $this;
    }
}
