<?php

namespace App\Entity;

use App\Repository\AbsencesPersonnelsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbsencesPersonnelsRepository::class)]
class AbsencesPersonnels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'absencesPersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\Column]
    private ?float $heureAbsence = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAbsence = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'absencesPersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\ManyToOne(inversedBy: 'absencesPersonnelsSaisie')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getHeureAbsence(): ?float
    {
        return $this->heureAbsence;
    }

    public function setHeureAbsence(float $heureAbsence): static
    {
        $this->heureAbsence = $heureAbsence;

        return $this;
    }

    public function getDateAbsence(): ?\DateTimeInterface
    {
        return $this->dateAbsence;
    }

    public function setDateAbsence(\DateTimeInterface $dateAbsence): static
    {
        $this->dateAbsence = $dateAbsence;

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

    public function getLieuVente(): ?LieuxVentes
    {
        return $this->lieuVente;
    }

    public function setLieuVente(?LieuxVentes $lieuVente): static
    {
        $this->lieuVente = $lieuVente;

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
}
