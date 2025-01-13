<?php

namespace App\Entity;

use App\Repository\CommissionVenteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommissionVenteRepository::class)]
class CommissionVente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commissionVentes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Facturation $facture = null;

    #[ORM\ManyToOne(inversedBy: 'commissionVentes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $beneficiaire = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePaiement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacture(): ?Facturation
    {
        return $this->facture;
    }

    public function setFacture(?Facturation $facture): static
    {
        $this->facture = $facture;

        return $this;
    }

    public function getBeneficiaire(): ?User
    {
        return $this->beneficiaire;
    }

    public function setBeneficiaire(?User $beneficiaire): static
    {
        $this->beneficiaire = $beneficiaire;

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

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): static
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }
}
