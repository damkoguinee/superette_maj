<?php

namespace App\Entity;

use App\Repository\SuppressionFactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuppressionFactureRepository::class)]
class SuppressionFacture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $numeroFacture = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $totalFacture = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montantPaye = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montantRemise = null;

    #[ORM\Column(length: 15)]
    private ?string $etat = null;

    #[ORM\Column(length: 15)]
    private ?string $etatLivraison = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateFacturation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomClientCash = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $fraisSup = null;

    #[ORM\ManyToOne(inversedBy: 'suppressionFacturesClient')]
    private ?User $client = null;

    #[ORM\ManyToOne(inversedBy: 'suppressionFactures')]
    private ?Caisse $caisse = null;

    #[ORM\ManyToOne(inversedBy: 'suppressionFactures')]
    private ?ModePaiement $modePaie = null;

    #[ORM\ManyToOne(inversedBy: 'suppressionFactures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\ManyToOne(inversedBy: 'suppressionFactures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroFacture(): ?string
    {
        return $this->numeroFacture;
    }

    public function setNumeroFacture(string $numeroFacture): static
    {
        $this->numeroFacture = $numeroFacture;

        return $this;
    }

    public function getTotalFacture(): ?string
    {
        return $this->totalFacture;
    }

    public function setTotalFacture(?string $totalFacture): static
    {
        $this->totalFacture = $totalFacture;

        return $this;
    }

    public function getMontantPaye(): ?string
    {
        return $this->montantPaye;
    }

    public function setMontantPaye(?string $montantPaye): static
    {
        $this->montantPaye = $montantPaye;

        return $this;
    }

    public function getMontantRemise(): ?string
    {
        return $this->montantRemise;
    }

    public function setMontantRemise(string $montantRemise): static
    {
        $this->montantRemise = $montantRemise;

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

    public function getEtatLivraison(): ?string
    {
        return $this->etatLivraison;
    }

    public function setEtatLivraison(string $etatLivraison): static
    {
        $this->etatLivraison = $etatLivraison;

        return $this;
    }

    public function getDateFacturation(): ?\DateTimeInterface
    {
        return $this->dateFacturation;
    }

    public function setDateFacturation(\DateTimeInterface $dateFacturation): static
    {
        $this->dateFacturation = $dateFacturation;

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

    public function getNomClientCash(): ?string
    {
        return $this->nomClientCash;
    }

    public function setNomClientCash(?string $nomClientCash): static
    {
        $this->nomClientCash = $nomClientCash;

        return $this;
    }

    public function getFraisSup(): ?string
    {
        return $this->fraisSup;
    }

    public function setFraisSup(?string $fraisSup): static
    {
        $this->fraisSup = $fraisSup;

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

    public function getCaisse(): ?Caisse
    {
        return $this->caisse;
    }

    public function setCaisse(?Caisse $caisse): static
    {
        $this->caisse = $caisse;

        return $this;
    }

    public function getModePaie(): ?ModePaiement
    {
        return $this->modePaie;
    }

    public function setModePaie(?ModePaiement $modePaie): static
    {
        $this->modePaie = $modePaie;

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
