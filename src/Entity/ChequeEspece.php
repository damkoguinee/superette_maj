<?php

namespace App\Entity;

use App\Repository\ChequeEspeceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChequeEspeceRepository::class)]
class ChequeEspece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chequeEspeces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $collaborateur = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montantCheque = null;

    #[ORM\Column(length: 100)]
    private ?string $numeroCheque = null;

    #[ORM\Column(length: 100)]
    private ?string $banqueCheque = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montantRecu = null;

    #[ORM\ManyToOne(inversedBy: 'chequeEspeces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Caisse $caisseDepot = null;

    #[ORM\ManyToOne(inversedBy: 'chequeEspeces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Caisse $caisseRetrait = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOperation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'chequeEspeces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'chequeEspeces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\OneToMany(mappedBy: 'chequeEspece', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade : ['persist', 'remove'])]
    private Collection $mouvementCaisses;

    #[ORM\OneToMany(mappedBy: 'chequeEspece', targetEntity: MouvementCollaborateur::class, orphanRemoval:true, cascade : ['persist', 'remove'])]
    private Collection $mouvementCollaborateurs;

    public function __construct()
    {
        $this->mouvementCaisses = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
    }

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

    public function getMontantCheque(): ?string
    {
        return $this->montantCheque;
    }

    public function setMontantCheque(string $montantCheque): static
    {
        $this->montantCheque = $montantCheque;

        return $this;
    }

    public function getNumeroCheque(): ?string
    {
        return $this->numeroCheque;
    }

    public function setNumeroCheque(string $numeroCheque): static
    {
        $this->numeroCheque = $numeroCheque;

        return $this;
    }

    public function getBanqueCheque(): ?string
    {
        return $this->banqueCheque;
    }

    public function setBanqueCheque(string $banqueCheque): static
    {
        $this->banqueCheque = $banqueCheque;

        return $this;
    }

    public function getMontantRecu(): ?string
    {
        return $this->montantRecu;
    }

    public function setMontantRecu(string $montantRecu): static
    {
        $this->montantRecu = $montantRecu;

        return $this;
    }

    public function getCaisseDepot(): ?Caisse
    {
        return $this->caisseDepot;
    }

    public function setCaisseDepot(?Caisse $caisseDepot): static
    {
        $this->caisseDepot = $caisseDepot;

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

    public function getSaisiePar(): ?User
    {
        return $this->saisiePar;
    }

    public function setSaisiePar(?User $saisiePar): static
    {
        $this->saisiePar = $saisiePar;

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
            $mouvementCaiss->setChequeEspece($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getChequeEspece() === $this) {
                $mouvementCaiss->setChequeEspece(null);
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
            $mouvementCollaborateur->setChequeEspece($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getChequeEspece() === $this) {
                $mouvementCollaborateur->setChequeEspece(null);
            }
        }

        return $this;
    }
}
