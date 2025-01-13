<?php

namespace App\Entity;

use App\Repository\ProformatFraisSupRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProformatFraisSupRepository::class)]
class ProformatFraisSup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'proformatFraisSups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Proformat $proformat = null;

    #[ORM\ManyToOne(inversedBy: 'proformatFraisSups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FraisSup $fraisSup = null;

    #[ORM\ManyToOne(inversedBy: 'proformatFraisSups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProformat(): ?Proformat
    {
        return $this->proformat;
    }

    public function setProformat(?Proformat $proformat): static
    {
        $this->proformat = $proformat;

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

    public function getDevise(): ?Devise
    {
        return $this->devise;
    }

    public function setDevise(?Devise $devise): static
    {
        $this->devise = $devise;

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
}
