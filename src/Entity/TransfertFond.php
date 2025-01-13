<?php

namespace App\Entity;

use App\Repository\TransfertFondRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransfertFondRepository::class)]
class TransfertFond
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\ManyToOne(inversedBy: 'transfertFonds')]
    private ?Devise $devise = null;

    #[ORM\ManyToOne(inversedBy: 'transfertFonds')]
    private ?Caisse $caisseDepart = null;

    #[ORM\ManyToOne(inversedBy: 'transfertFonds')]
    private ?Caisse $caisseReception = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'transfertFonds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\ManyToOne(inversedBy: 'transfertFonds')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $traitePar = null;

    #[ORM\ManyToOne(inversedBy: 'transfertFonds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $envoyePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOperation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $document = null;

    #[ORM\Column(length: 20)]
    private ?string $etat = null;

    #[ORM\OneToMany(mappedBy: 'transfertFond', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade : ['persist', 'remove'])]
    private Collection $mouvementCaisses;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'transferyFondsRecep')]
    private ?LieuxVentes $lieuVenteReception = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReception = null;

    public function __construct()
    {
        $this->mouvementCaisses = new ArrayCollection();
    }

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

    public function getCaisseDepart(): ?Caisse
    {
        return $this->caisseDepart;
    }

    public function setCaisseDepart(?Caisse $caisseDepart): static
    {
        $this->caisseDepart = $caisseDepart;

        return $this;
    }

    public function getCaisseReception(): ?Caisse
    {
        return $this->caisseReception;
    }

    public function setCaisseReception(?Caisse $caisseReception): static
    {
        $this->caisseReception = $caisseReception;

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

    public function getEnvoyePar(): ?User
    {
        return $this->envoyePar;
    }

    public function setEnvoyePar(?User $envoyePar): static
    {
        $this->envoyePar = $envoyePar;

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

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): static
    {
        $this->document = $document;

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
            $mouvementCaiss->setTransfertFond($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getTransfertFond() === $this) {
                $mouvementCaiss->setTransfertFond(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLieuVenteReception(): ?LieuxVentes
    {
        return $this->lieuVenteReception;
    }

    public function setLieuVenteReception(?LieuxVentes $lieuVenteReception): static
    {
        $this->lieuVenteReception = $lieuVenteReception;

        return $this;
    }

    public function getDateReception(): ?\DateTimeInterface
    {
        return $this->dateReception;
    }

    public function setDateReception(?\DateTimeInterface $dateReception): static
    {
        $this->dateReception = $dateReception;

        return $this;
    }
}
