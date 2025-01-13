<?php

namespace App\Entity;

use App\Repository\PaiementSalairePersonnelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementSalairePersonnelRepository::class)]
class PaiementSalairePersonnel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paiementSalairePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\ManyToOne(inversedBy: 'paiementSalairePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $periode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaires = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $salaireBrut = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $prime = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $avanceSalaire = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $cotisation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $salaireNet = null;

    #[ORM\ManyToOne(inversedBy: 'paiementSalairePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Caisse $compteRetrait = null;

    #[ORM\ManyToOne(inversedBy: 'paiementSalairePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ModePaiement $typePaiement = null;

    #[ORM\ManyToOne(inversedBy: 'paiementSalairePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

    #[ORM\ManyToOne(inversedBy: 'paiementSalairePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\OneToMany(mappedBy: 'paiementSalaire', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade : ['persist', 'remove'])]
    private Collection $mouvementCaisses;

    #[ORM\Column(nullable: true)]
    private ?float $heures = null;

    public function __construct()
    {
        $this->mouvementCaisses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPeriode(): ?\DateTimeInterface
    {
        return $this->periode;
    }

    public function setPeriode(\DateTimeInterface $periode): static
    {
        $this->periode = $periode;

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

    public function getCommentaires(): ?string
    {
        return $this->commentaires;
    }

    public function setCommentaires(?string $commentaires): static
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    public function getSalaireBrut(): ?string
    {
        return $this->salaireBrut;
    }

    public function setSalaireBrut(string $salaireBrut): static
    {
        $this->salaireBrut = $salaireBrut;

        return $this;
    }

    public function getPrime(): ?string
    {
        return $this->prime;
    }

    public function setPrime(?string $prime): static
    {
        $this->prime = $prime;

        return $this;
    }

    public function getAvanceSalaire(): ?string
    {
        return $this->avanceSalaire;
    }

    public function setAvanceSalaire(?string $avanceSalaire): static
    {
        $this->avanceSalaire = $avanceSalaire;

        return $this;
    }

    public function getCotisation(): ?string
    {
        return $this->cotisation;
    }

    public function setCotisation(?string $cotisation): static
    {
        $this->cotisation = $cotisation;

        return $this;
    }

    public function getSalaireNet(): ?string
    {
        return $this->salaireNet;
    }

    public function setSalaireNet(string $salaireNet): static
    {
        $this->salaireNet = $salaireNet;

        return $this;
    }

    public function getCompteRetrait(): ?Caisse
    {
        return $this->compteRetrait;
    }

    public function setCompteRetrait(?Caisse $compteRetrait): static
    {
        $this->compteRetrait = $compteRetrait;

        return $this;
    }

    public function getTypePaiement(): ?ModePaiement
    {
        return $this->typePaiement;
    }

    public function setTypePaiement(?ModePaiement $typePaiement): static
    {
        $this->typePaiement = $typePaiement;

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
    public function getLieuVente(): ?LieuxVentes
    {
        return $this->lieuVente;
    }

    public function setLieuVente(?LieuxVentes $lieuVente): static
    {
        $this->lieuVente = $lieuVente;

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
            $mouvementCaiss->setPaiementSalaire($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getPaiementSalaire() === $this) {
                $mouvementCaiss->setPaiementSalaire(null);
            }
        }

        return $this;
    }

    public function getHeures(): ?float
    {
        return $this->heures;
    }

    public function setHeures(?float $heures): static
    {
        $this->heures = $heures;

        return $this;
    }

    
}
