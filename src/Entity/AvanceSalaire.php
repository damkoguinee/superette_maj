<?php

namespace App\Entity;

use App\Repository\AvanceSalaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvanceSalaireRepository::class)]
class AvanceSalaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'avanceSalaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $montant = null;

    

    #[ORM\ManyToOne(inversedBy: 'avanceSalaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

    

    #[ORM\Column(length: 10)]
    private ?string $mois = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAvance = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $periode = null;

    #[ORM\ManyToOne(inversedBy: 'avanceSalaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ModePaiement $modePaiement = null;

    #[ORM\ManyToOne(inversedBy: 'avanceSalaireTraite')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $traitePar = null;

    #[ORM\OneToMany(mappedBy: 'avanceSalaire', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade :['persist'])]
    private Collection $mouvementCaisses;

    #[ORM\ManyToOne(inversedBy: 'avanceSalaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Caisse $caisse = null;

    #[ORM\ManyToOne(inversedBy: 'avanceSalaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\OneToMany(mappedBy: 'avanceSalaire', targetEntity: MouvementCollaborateur::class)]
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getMois(): ?string
    {
        return $this->mois;
    }

    public function setMois(string $mois): static
    {
        $this->mois = $mois;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getDateAvance(): ?\DateTimeInterface
    {
        return $this->dateAvance;
    }

    public function setDateAvance(\DateTimeInterface $dateAvance): static
    {
        $this->dateAvance = $dateAvance;

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

    public function getModePaiement(): ?ModePaiement
    {
        return $this->modePaiement;
    }

    public function setModePaiement(?ModePaiement $modePaiement): static
    {
        $this->modePaiement = $modePaiement;

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
            $mouvementCaiss->setAvanceSalaire($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getAvanceSalaire() === $this) {
                $mouvementCaiss->setAvanceSalaire(null);
            }
        }

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
            $mouvementCollaborateur->setAvanceSalaire($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getAvanceSalaire() === $this) {
                $mouvementCollaborateur->setAvanceSalaire(null);
            }
        }

        return $this;
    }
}
