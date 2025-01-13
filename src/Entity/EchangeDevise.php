<?php

namespace App\Entity;

use App\Repository\EchangeDeviseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EchangeDeviseRepository::class)]
class EchangeDevise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montantOrigine = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montantDestination = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $taux = null;

    #[ORM\Column(length: 255)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEchange = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'echangeDevises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\ManyToOne(inversedBy: 'echangeDevises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\ManyToOne(inversedBy: 'echangeDevises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Caisse $caisseOrigine = null;

    #[ORM\ManyToOne(inversedBy: 'echangeDevises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Caisse $caisseDestination = null;

    #[ORM\OneToMany(mappedBy: 'echange', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementCaisses;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $document = null;

    #[ORM\ManyToOne(inversedBy: 'echangeDevises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $deviseOrigine = null;

    #[ORM\ManyToOne(inversedBy: 'echangeDevises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devise $deviseDestination = null;

    public function __construct()
    {
        $this->mouvementCaisses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontantOrigine(): ?string
    {
        return $this->montantOrigine;
    }

    public function setMontantOrigine(string $montantOrigine): static
    {
        $this->montantOrigine = $montantOrigine;

        return $this;
    }

    public function getMontantDestination(): ?string
    {
        return $this->montantDestination;
    }

    public function setMontantDestination(string $montantDestination): static
    {
        $this->montantDestination = $montantDestination;

        return $this;
    }

    

    

    public function getTaux(): ?string
    {
        return $this->taux;
    }

    public function setTaux(string $taux): static
    {
        $this->taux = $taux;

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

    public function getDateEchange(): ?\DateTimeInterface
    {
        return $this->dateEchange;
    }

    public function setDateEchange(\DateTimeInterface $dateEchange): static
    {
        $this->dateEchange = $dateEchange;

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

    public function getLieuVente(): ?LieuxVentes
    {
        return $this->lieuVente;
    }

    public function setLieuVente(?LieuxVentes $lieuVente): static
    {
        $this->lieuVente = $lieuVente;

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

    public function getCaisseOrigine(): ?Caisse
    {
        return $this->caisseOrigine;
    }

    public function setCaisseOrigine(?Caisse $caisseOrigine): static
    {
        $this->caisseOrigine = $caisseOrigine;

        return $this;
    }

    public function getCaisseDestination(): ?Caisse
    {
        return $this->caisseDestination;
    }

    public function setCaisseDestination(?Caisse $caisseDestination): static
    {
        $this->caisseDestination = $caisseDestination;

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
            $mouvementCaiss->setEchange($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getEchange() === $this) {
                $mouvementCaiss->setEchange(null);
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

    public function getDeviseOrigine(): ?Devise
    {
        return $this->deviseOrigine;
    }

    public function setDeviseOrigine(?Devise $deviseOrigine): static
    {
        $this->deviseOrigine = $deviseOrigine;

        return $this;
    }

    public function getDeviseDestination(): ?Devise
    {
        return $this->deviseDestination;
    }

    public function setDeviseDestination(?Devise $deviseDestination): static
    {
        $this->deviseDestination = $deviseDestination;

        return $this;
    }
}
