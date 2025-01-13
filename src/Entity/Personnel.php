<?php

namespace App\Entity;

use App\Repository\PersonnelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonnelRepository::class)]
class Personnel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'personnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $fonction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentIdentite = null;

    #[ORM\Column(length: 50)]
    private ?string $typePaie = null;

    #[ORM\Column(nullable: true)]
    private ?float $tauxHoraire = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $salaireBase = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoIdentite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signature = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getDocumentIdentite(): ?string
    {
        return $this->documentIdentite;
    }

    public function setDocumentIdentite(?string $documentIdentite): static
    {
        $this->documentIdentite = $documentIdentite;

        return $this;
    }

    public function getTypePaie(): ?string
    {
        return $this->typePaie;
    }

    public function setTypePaie(string $typePaie): static
    {
        $this->typePaie = $typePaie;

        return $this;
    }

    public function getTauxHoraire(): ?float
    {
        return $this->tauxHoraire;
    }

    public function setTauxHoraire(?float $tauxHoraire): static
    {
        $this->tauxHoraire = $tauxHoraire;

        return $this;
    }

    public function getSalaireBase(): ?string
    {
        return $this->salaireBase;
    }

    public function setSalaireBase(?string $salaireBase): static
    {
        $this->salaireBase = $salaireBase;

        return $this;
    }

    public function getPhotoIdentite(): ?string
    {
        return $this->photoIdentite;
    }

    public function setPhotoIdentite(?string $photoIdentite): static
    {
        $this->photoIdentite = $photoIdentite;

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): static
    {
        $this->signature = $signature;

        return $this;
    }
}
