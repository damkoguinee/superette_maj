<?php

namespace App\Entity;

use App\Repository\DecaissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DecaissementRepository::class)]
class Decaissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'decaissements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne(inversedBy: 'decaissements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\ManyToOne(inversedBy: 'decaissementPersonnel')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $traitePar = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montant = null;

    #[ORM\ManyToOne(inversedBy: 'decaissements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

    #[ORM\ManyToOne(inversedBy: 'decaissements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ModePaiement $modePaie = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numeroChequeBord = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $banqueCheque = null;

    #[ORM\ManyToOne(inversedBy: 'decaissements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Caisse $compteDecaisser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDecaissement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'decaissements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategorieDecaissement $categorie = null;

    #[ORM\OneToMany(mappedBy: 'decaissement', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementCaisses;

    #[ORM\OneToMany(mappedBy: 'decaissement', targetEntity: MouvementCollaborateur::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementCollaborateurs;

    #[ORM\OneToMany(mappedBy: 'decaissement', targetEntity: ModifDecaissement::class)]
    private Collection $modifDecaissements;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $document = null;

    #[ORM\OneToMany(mappedBy: 'decaissement', targetEntity: Modification::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $modifications;

    public function __construct()
    {
        $this->mouvementCaisses = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->modifDecaissements = new ArrayCollection();
        $this->modifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDevise(): ?Devise
    {
        return $this->devise;
    }

    public function setDevise(?Devise $devise): static
    {
        $this->devise = $devise;

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

    public function getCompteDecaisser(): ?Caisse
    {
        return $this->compteDecaisser;
    }

    public function setCompteDecaisser(?Caisse $compteDecaisser): static
    {
        $this->compteDecaisser = $compteDecaisser;

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

    public function getCategorie(): ?CategorieDecaissement
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieDecaissement $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, MouvementCaisse>
     */
    public function getMouvementCaisses(): Collection
    {
        return $this->mouvementCaisses;
    }

    public function addMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if (!$this->mouvementCaisses->contains($mouvementCaiss)) {
            $this->mouvementCaisses->add($mouvementCaiss);
            $mouvementCaiss->setDecaissement($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getDecaissement() === $this) {
                $mouvementCaiss->setDecaissement(null);
            }
        }

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
            $mouvementCollaborateur->setDecaissement($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getDecaissement() === $this) {
                $mouvementCollaborateur->setDecaissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ModifDecaissement>
     */
    public function getModifDecaissements(): Collection
    {
        return $this->modifDecaissements;
    }

    public function addModifDecaissement(ModifDecaissement $modifDecaissement): static
    {
        if (!$this->modifDecaissements->contains($modifDecaissement)) {
            $this->modifDecaissements->add($modifDecaissement);
            $modifDecaissement->setDecaissement($this);
        }

        return $this;
    }

    public function removeModifDecaissement(ModifDecaissement $modifDecaissement): static
    {
        if ($this->modifDecaissements->removeElement($modifDecaissement)) {
            // set the owning side to null (unless already changed)
            if ($modifDecaissement->getDecaissement() === $this) {
                $modifDecaissement->setDecaissement(null);
            }
        }

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

    /**
     * @return Collection<int, Modification>
     */
    public function getModifications(): Collection
    {
        return $this->modifications;
    }

    public function addModification(Modification $modification): static
    {
        if (!$this->modifications->contains($modification)) {
            $this->modifications->add($modification);
            $modification->setDecaissement($this);
        }

        return $this;
    }

    public function removeModification(Modification $modification): static
    {
        if ($this->modifications->removeElement($modification)) {
            // set the owning side to null (unless already changed)
            if ($modification->getDecaissement() === $this) {
                $modification->setDecaissement(null);
            }
        }

        return $this;
    }
}
