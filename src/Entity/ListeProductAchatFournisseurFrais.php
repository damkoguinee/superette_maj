<?php

namespace App\Entity;

use App\Repository\ListeProductAchatFournisseurFraisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeProductAchatFournisseurFraisRepository::class)]
class ListeProductAchatFournisseurFrais
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurFrais')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AchatFournisseur $achatFournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurFrais')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurFrais')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixAchat = null;

    #[ORM\Column(nullable: true)]
    private ?float $frais = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(nullable: true)]
    private ?float $taux = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixVente = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAchatFournisseur(): ?AchatFournisseur
    {
        return $this->achatFournisseur;
    }

    public function setAchatFournisseur(?AchatFournisseur $achatFournisseur): static
    {
        $this->achatFournisseur = $achatFournisseur;

        return $this;
    }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): static
    {
        $this->product = $product;

        return $this;
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

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(?float $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrixAchat(): ?string
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?string $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getFrais(): ?float
    {
        return $this->frais;
    }

    public function setFrais(?float $frais): static
    {
        $this->frais = $frais;

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

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(?float $taux): static
    {
        $this->taux = $taux;

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

    public function getPrixVente(): ?string
    {
        return $this->prixVente;
    }

    public function setPrixVente(?string $prixVente): static
    {
        $this->prixVente = $prixVente;

        return $this;
    }
}
