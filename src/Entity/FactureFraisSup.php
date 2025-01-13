<?php

namespace App\Entity;

use App\Repository\FactureFraisSupRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureFraisSupRepository::class)]
class FactureFraisSup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'factureFraisSups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Facturation $facturation = null;

    #[ORM\ManyToOne(inversedBy: 'factureFraisSups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FraisSup $fraisSup = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montant = null;

    #[ORM\ManyToOne(inversedBy: 'factureFraisSups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

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

    public function getFraisSup(): ?FraisSup
    {
        return $this->fraisSup;
    }

    public function setFraisSup(?FraisSup $fraisSup): static
    {
        $this->fraisSup = $fraisSup;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
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
}
