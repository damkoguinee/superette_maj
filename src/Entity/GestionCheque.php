<?php

namespace App\Entity;

use App\Repository\GestionChequeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GestionChequeRepository::class)]
class GestionCheque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'gestionCheques')]
    private ?Caisse $caisseDepart = null;

    #[ORM\ManyToOne(inversedBy: 'gestionCheques')]
    private ?Caisse $caisseReception = null;

    #[ORM\ManyToOne(inversedBy: 'gestionCheques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVenteDepart = null;

    #[ORM\ManyToOne(inversedBy: 'gestionCheques')]
    private ?LieuxVentes $lieuVenteReception = null;

    #[ORM\ManyToOne(inversedBy: 'gestionCheques')]
    private ?User $traitePar = null;

    #[ORM\ManyToOne(inversedBy: 'gestionCheques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $envoyePar = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montant = null;

    #[ORM\ManyToOne(inversedBy: 'gestionCheques')]
    private ?User $collaborateur = null;

    #[ORM\ManyToOne(inversedBy: 'gestionCheques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MouvementCaisse $mouvementCaisse = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOperation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(length: 10)]
    private ?string $etat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReception = null;

    #[ORM\OneToMany(mappedBy: 'transfertCheque', targetEntity: MouvementCollaborateur::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementCollaborateurs;

    public function __construct()
    {
        $this->mouvementCollaborateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLieuVenteDepart(): ?LieuxVentes
    {
        return $this->lieuVenteDepart;
    }

    public function setLieuVenteDepart(?LieuxVentes $lieuVenteDepart): static
    {
        $this->lieuVenteDepart = $lieuVenteDepart;

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

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
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

    public function getMouvementCaisse(): ?MouvementCaisse
    {
        return $this->mouvementCaisse;
    }

    public function setMouvementCaisse(?MouvementCaisse $mouvementCaisse): static
    {
        $this->mouvementCaisse = $mouvementCaisse;

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

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

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
            $mouvementCollaborateur->setTransfertCheque($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getTransfertCheque() === $this) {
                $mouvementCollaborateur->setTransfertCheque(null);
            }
        }

        return $this;
    }

    
}
