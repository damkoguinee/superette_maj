<?php

namespace App\Entity;

use App\Repository\DeleteDecaissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeleteDecaissementRepository::class)]
class DeleteDecaissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montant = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numeroChequeBord = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $banqueCheque = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDecaissement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $client = null;

    #[ORM\Column(length: 255)]
    private ?string $traitePar = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $devise = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $caisse = null;

    #[ORM\Column(length: 255)]
    private ?string $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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

    

    public function getNumeroChequeBord(): ?string
    {
        return $this->numeroChequeBord;
    }

    public function setNumeroChequeBord(?string $numeroChequeBord): static
    {
        $this->numeroChequeBord = $numeroChequeBord;

        return $this;
    }

    public function getBanqueCheque(): ?string
    {
        return $this->banqueCheque;
    }

    public function setBanqueCheque(?string $banqueCheque): static
    {
        $this->banqueCheque = $banqueCheque;

        return $this;
    }

    public function getDateDecaissement(): ?\DateTimeInterface
    {
        return $this->dateDecaissement;
    }

    public function setDateDecaissement(\DateTimeInterface $dateDecaissement): static
    {
        $this->dateDecaissement = $dateDecaissement;

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

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(string $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getTraitePar(): ?string
    {
        return $this->traitePar;
    }

    public function setTraitePar(string $traitePar): static
    {
        $this->traitePar = $traitePar;

        return $this;
    }

    public function getDevise(): ?string
    {
        return $this->devise;
    }

    public function setDevise(?string $devise): static
    {
        $this->devise = $devise;

        return $this;
    }

    public function getCaisse(): ?string
    {
        return $this->caisse;
    }

    public function setCaisse(?string $caisse): static
    {
        $this->caisse = $caisse;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }
}
