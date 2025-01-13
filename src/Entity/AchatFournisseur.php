<?php

namespace App\Entity;

use App\Repository\AchatFournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchatFournisseurRepository::class)]
class AchatFournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'achatFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $fournisseur = null;

    #[ORM\Column(length: 100)]
    private ?string $numeroFacture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeProduct = null;

    #[ORM\Column(length: 255)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\ManyToOne(inversedBy: 'achatFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFacture = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'achatFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

    #[ORM\Column]
    private ?float $taux = null;

    #[ORM\OneToMany(mappedBy: 'achatFournisseur', targetEntity: ListeProductAchatFournisseur::class, orphanRemoval: true)]
    private Collection $listeProductAchatFournisseurs;

    #[ORM\ManyToOne(inversedBy: 'achat')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $document = null;

    #[ORM\Column(length: 15)]
    private ?string $etatPaiement = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $tva = null;

    #[ORM\OneToMany(mappedBy: 'achatFournisseur', targetEntity: MouvementCollaborateur::class, orphanRemoval:true, cascade:['persist','remove'])]
    private Collection $mouvementCollaborateurs;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $typeSaisie = null;

    #[ORM\OneToMany(mappedBy: 'achatFournisseur', targetEntity: ListeProductAchatFournisseurFrais::class, orphanRemoval:true, cascade:['persist','remove'])]
    private Collection $listeProductAchatFournisseurFrais;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $etatReception = null;

    public function __construct()
    {
        $this->listeProductAchatFournisseurs = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->listeProductAchatFournisseurFrais = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFournisseur(): ?User
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?User $fournisseur): static
    {
        $this->fournisseur = $fournisseur;

        return $this;
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

    public function getTypeProduct(): ?string
    {
        return $this->typeProduct;
    }

    public function setTypeProduct(?string $typeProduct): static
    {
        $this->typeProduct = $typeProduct;

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

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(?string $montant): static
    {
        $this->montant = $montant;

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

    public function getDateFacture(): ?\DateTimeInterface
    {
        return $this->dateFacture;
    }

    public function setDateFacture(\DateTimeInterface $dateFacture): static
    {
        $this->dateFacture = $dateFacture;

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

    public function getDevise(): ?Devise
    {
        return $this->devise;
    }

    public function setDevise(?Devise $devise): static
    {
        $this->devise = $devise;

        return $this;
    }

    /**
     * @return Collection<int, ListeProductAchatFournisseur>
     */
    public function getListeProductAchatFournisseurs(): Collection
    {
        return $this->listeProductAchatFournisseurs;
    }

    public function addListeProductAchatFournisseur(ListeProductAchatFournisseur $listeProductAchatFournisseur): static
    {
        if (!$this->listeProductAchatFournisseurs->contains($listeProductAchatFournisseur)) {
            $this->listeProductAchatFournisseurs->add($listeProductAchatFournisseur);
            $listeProductAchatFournisseur->setAchatFournisseur($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseur(ListeProductAchatFournisseur $listeProductAchatFournisseur): static
    {
        if ($this->listeProductAchatFournisseurs->removeElement($listeProductAchatFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseur->getAchatFournisseur() === $this) {
                $listeProductAchatFournisseur->setAchatFournisseur(null);
            }
        }

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

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getEtatPaiement(): ?string
    {
        return $this->etatPaiement;
    }

    public function setEtatPaiement(string $etatPaiement): static
    {
        $this->etatPaiement = $etatPaiement;

        return $this;
    }

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(float $taux): static
    {
        $this->taux = $taux;

        return $this;
    }

    public function getTva(): ?string
    {
        return $this->tva;
    }

    public function setTva(?string $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * @return Collection<int, MouvementCollaborateur>
     */
    public function getMouvementCollaborateurs(): Collection
    {
        return $this->mouvementCollaborateurs;
    }

    public function addMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if (!$this->mouvementCollaborateurs->contains($mouvementCollaborateur)) {
            $this->mouvementCollaborateurs->add($mouvementCollaborateur);
            $mouvementCollaborateur->setAchatFournisseur($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getAchatFournisseur() === $this) {
                $mouvementCollaborateur->setAchatFournisseur(null);
            }
        }

        return $this;
    }

    public function getTypeSaisie(): ?string
    {
        return $this->typeSaisie;
    }

    public function setTypeSaisie(?string $typeSaisie): static
    {
        $this->typeSaisie = $typeSaisie;

        return $this;
    }

    /**
     * @return Collection<int, ListeProductAchatFournisseurFrais>
     */
    public function getListeProductAchatFournisseurFrais(): Collection
    {
        return $this->listeProductAchatFournisseurFrais;
    }

    public function addListeProductAchatFournisseurFrai(ListeProductAchatFournisseurFrais $listeProductAchatFournisseurFrai): static
    {
        if (!$this->listeProductAchatFournisseurFrais->contains($listeProductAchatFournisseurFrai)) {
            $this->listeProductAchatFournisseurFrais->add($listeProductAchatFournisseurFrai);
            $listeProductAchatFournisseurFrai->setAchatFournisseur($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseurFrai(ListeProductAchatFournisseurFrais $listeProductAchatFournisseurFrai): static
    {
        if ($this->listeProductAchatFournisseurFrais->removeElement($listeProductAchatFournisseurFrai)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseurFrai->getAchatFournisseur() === $this) {
                $listeProductAchatFournisseurFrai->setAchatFournisseur(null);
            }
        }

        return $this;
    }

    public function getEtatReception(): ?string
    {
        return $this->etatReception;
    }

    public function setEtatReception(?string $etatReception): static
    {
        $this->etatReception = $etatReception;

        return $this;
    }
}
