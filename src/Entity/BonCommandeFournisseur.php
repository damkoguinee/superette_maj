<?php

namespace App\Entity;

use App\Repository\BonCommandeFournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BonCommandeFournisseurRepository::class)]
class BonCommandeFournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bonCommandeFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $fournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'bonCommandeFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\ManyToOne(inversedBy: 'bonCommandeFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

    #[ORM\Column(length: 255)]
    private ?string $numeroBon = null;

    #[ORM\Column(length: 255)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'bonCommandeFournisseursPersonnel')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\OneToMany(mappedBy: 'bonFournisseur', targetEntity: ListeProductBonFournisseur::class)]
    private Collection $listeProductBonFournisseurs;

    public function __construct()
    {
        $this->listeProductBonFournisseurs = new ArrayCollection();
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

    public function getLieuVente(): ?LieuxVentes
    {
        return $this->lieuVente;
    }

    public function setLieuVente(?LieuxVentes $lieuVente): static
    {
        $this->lieuVente = $lieuVente;

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

    public function getNumeroBon(): ?string
    {
        return $this->numeroBon;
    }

    public function setNumeroBon(string $numeroBon): static
    {
        $this->numeroBon = $numeroBon;

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

    public function getDateSaisie(): ?\DateTimeInterface
    {
        return $this->dateSaisie;
    }

    public function setDateSaisie(\DateTimeInterface $dateSaisie): static
    {
        $this->dateSaisie = $dateSaisie;

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

    /**
     * @return Collection<int, ListeProductBonFournisseur>
     */
    public function getListeProductBonFournisseurs(): Collection
    {
        return $this->listeProductBonFournisseurs;
    }

    public function addListeProductBonFournisseur(ListeProductBonFournisseur $listeProductBonFournisseur): static
    {
        if (!$this->listeProductBonFournisseurs->contains($listeProductBonFournisseur)) {
            $this->listeProductBonFournisseurs->add($listeProductBonFournisseur);
            $listeProductBonFournisseur->setBonFournisseur($this);
        }

        return $this;
    }

    public function removeListeProductBonFournisseur(ListeProductBonFournisseur $listeProductBonFournisseur): static
    {
        if ($this->listeProductBonFournisseurs->removeElement($listeProductBonFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($listeProductBonFournisseur->getBonFournisseur() === $this) {
                $listeProductBonFournisseur->setBonFournisseur(null);
            }
        }

        return $this;
    }
}
