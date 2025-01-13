<?php

namespace App\Entity;

use App\Repository\DepensesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepensesRepository::class)]
class Depenses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 2)]
    private ?string $montant = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ModePaiement $modePaiement = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Caisse $caisseRetrait = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategorieDepense $categorieDepense = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $traitePar = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDepense = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $justificatif = null;

    #[ORM\OneToMany(mappedBy: 'depense', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade : ['persist', 'remove'])]
    private Collection $mouvementCaisses;

    #[ORM\OneToMany(mappedBy: 'depense', targetEntity: ModifDecaissement::class)]
    private Collection $modifDecaissements;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $tva = null;

    #[ORM\OneToMany(mappedBy: 'depense', targetEntity: Modification::class)]
    private Collection $modifications;

    public function __construct()
    {
        $this->mouvementCaisses = new ArrayCollection();
        $this->modifDecaissements = new ArrayCollection();
        $this->modifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    

    public function getDateDepense(): ?\DateTimeInterface
    {
        return $this->dateDepense;
    }

    public function setDateDepense(\DateTimeInterface $dateDepense): static
    {
        $this->dateDepense = $dateDepense;

        return $this;
    }

    
    public function getCategorieDepense(): ?CategorieDepense
    {
        return $this->categorieDepense;
    }

    public function setCategorieDepense(?CategorieDepense $categorieDepense): static
    {
        $this->categorieDepense = $categorieDepense;

        return $this;
    }

    public function getJustificatif(): ?string
    {
        return $this->justificatif;
    }

    public function setJustificatif(?string $justificatif): static
    {
        $this->justificatif = $justificatif;

        return $this;
    }

    public function getModePaiement(): ?ModePaiement
    {
        return $this->modePaiement;
    }

    public function setModePaiement(?ModePaiement $modePaiement): static
    {
        $this->modePaiement = $modePaiement;

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

    public function getCaisseRetrait(): ?Caisse
    {
        return $this->caisseRetrait;
    }

    public function setCaisseRetrait(?Caisse $caisseRetrait): static
    {
        $this->caisseRetrait = $caisseRetrait;

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

    public function getLieuVente(): ?LieuxVentes
    {
        return $this->lieuVente;
    }

    public function setLieuVente(?LieuxVentes $lieuVente): static
    {
        $this->lieuVente = $lieuVente;

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
            $mouvementCaiss->setDepense($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getDepense() === $this) {
                $mouvementCaiss->setDepense(null);
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
            $modifDecaissement->setDepense($this);
        }

        return $this;
    }

    public function removeModifDecaissement(ModifDecaissement $modifDecaissement): static
    {
        if ($this->modifDecaissements->removeElement($modifDecaissement)) {
            // set the owning side to null (unless already changed)
            if ($modifDecaissement->getDepense() === $this) {
                $modifDecaissement->setDepense(null);
            }
        }

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
            $modification->setDepense($this);
        }

        return $this;
    }

    public function removeModification(Modification $modification): static
    {
        if ($this->modifications->removeElement($modification)) {
            // set the owning side to null (unless already changed)
            if ($modification->getDepense() === $this) {
                $modification->setDepense(null);
            }
        }

        return $this;
    }
}
