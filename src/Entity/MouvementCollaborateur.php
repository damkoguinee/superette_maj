<?php

namespace App\Entity;

use App\Repository\MouvementCollaborateurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MouvementCollaborateurRepository::class)]
class MouvementCollaborateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $collaborateur = null;

    #[ORM\Column(length: 100)]
    private ?string $origine = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Caisse $caisse = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementPersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $traitePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOperation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?Decaissement $decaissement = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?AvanceSalaire $avanceSalaire = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?Versement $versement = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?ChequeEspece $chequeEspece = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?AjustementSolde $ajustement = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?AchatFournisseur $achatFournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?Facturation $facture = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?RetourProduct $retourProduct = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?GestionCheque $transfertCheque = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?RetourProductFournisseur $retourProductFournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCollaborateurs')]
    private ?FactureFrais $factureFrais = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrigine(): ?string
    {
        return $this->origine;
    }

    public function setOrigine(string $origine): static
    {
        $this->origine = $origine;

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

    public function getCaisse(): ?Caisse
    {
        return $this->caisse;
    }

    public function setCaisse(?Caisse $caisse): static
    {
        $this->caisse = $caisse;

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

    public function getTraitePar(): ?User
    {
        return $this->traitePar;
    }

    public function setTraitePar(?User $traitePar): static
    {
        $this->traitePar = $traitePar;

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

    public function getDateSaisie(): ?\DateTimeInterface
    {
        return $this->dateSaisie;
    }

    public function setDateSaisie(\DateTimeInterface $dateSaisie): static
    {
        $this->dateSaisie = $dateSaisie;

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

    public function getAvanceSalaire(): ?AvanceSalaire
    {
        return $this->avanceSalaire;
    }

    public function setAvanceSalaire(?AvanceSalaire $avanceSalaire): static
    {
        $this->avanceSalaire = $avanceSalaire;

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

    public function getChequeEspece(): ?ChequeEspece
    {
        return $this->chequeEspece;
    }

    public function setChequeEspece(?ChequeEspece $chequeEspece): static
    {
        $this->chequeEspece = $chequeEspece;

        return $this;
    }

    public function getAjustement(): ?AjustementSolde
    {
        return $this->ajustement;
    }

    public function setAjustement(?AjustementSolde $ajustement): static
    {
        $this->ajustement = $ajustement;

        return $this;
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

    public function getFacture(): ?Facturation
    {
        return $this->facture;
    }

    public function setFacture(?Facturation $facture): static
    {
        $this->facture = $facture;

        return $this;
    }

    public function getRetourProduct(): ?RetourProduct
    {
        return $this->retourProduct;
    }

    public function setRetourProduct(?RetourProduct $retourProduct): static
    {
        $this->retourProduct = $retourProduct;

        return $this;
    }

    public function getTransfertCheque(): ?GestionCheque
    {
        return $this->transfertCheque;
    }

    public function setTransfertCheque(?GestionCheque $transfertCheque): static
    {
        $this->transfertCheque = $transfertCheque;

        return $this;
    }

    public function getRetourProductFournisseur(): ?RetourProductFournisseur
    {
        return $this->retourProductFournisseur;
    }

    public function setRetourProductFournisseur(?RetourProductFournisseur $retourProductFournisseur): static
    {
        $this->retourProductFournisseur = $retourProductFournisseur;

        return $this;
    }

    public function getFactureFrais(): ?FactureFrais
    {
        return $this->factureFrais;
    }

    public function setFactureFrais(?FactureFrais $factureFrais): static
    {
        $this->factureFrais = $factureFrais;

        return $this;
    }
}
