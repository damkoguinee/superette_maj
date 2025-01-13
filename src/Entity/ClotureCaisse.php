<?php

namespace App\Entity;

use App\Repository\ClotureCaisseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClotureCaisseRepository::class)]
class ClotureCaisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $journee = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montantTheo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montantReel = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $difference = null;

    #[ORM\ManyToOne(inversedBy: 'clotureCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\ManyToOne(inversedBy: 'clotureCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Caisse $caisse = null;

    #[ORM\ManyToOne(inversedBy: 'clotureCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $devise = null;

    #[ORM\ManyToOne(inversedBy: 'clotureCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\OneToMany(mappedBy: 'cloture', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementCaisses;

    public function __construct()
    {
        $this->mouvementCaisses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJournee(): ?\DateTimeInterface
    {
        return $this->journee;
    }

    public function setJournee(\DateTimeInterface $journee): static
    {
        $this->journee = $journee;

        return $this;
    }

    public function getMontantTheo(): ?string
    {
        return $this->montantTheo;
    }

    public function setMontantTheo(string $montantTheo): static
    {
        $this->montantTheo = $montantTheo;

        return $this;
    }

    public function getMontantReel(): ?string
    {
        return $this->montantReel;
    }

    public function setMontantReel(string $montantReel): static
    {
        $this->montantReel = $montantReel;

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

    public function getDifference(): ?string
    {
        return $this->difference;
    }

    public function setDifference(string $difference): static
    {
        $this->difference = $difference;

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

    public function getCaisse(): ?Caisse
    {
        return $this->caisse;
    }

    public function setCaisse(?Caisse $caisse): static
    {
        $this->caisse = $caisse;

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
            $mouvementCaiss->setCloture($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getCloture() === $this) {
                $mouvementCaiss->setCloture(null);
            }
        }

        return $this;
    }
}
