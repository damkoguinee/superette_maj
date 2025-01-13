<?php

namespace App\Entity;

use App\Repository\FacturationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FacturationRepository::class)]
class Facturation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $numeroFacture = null;

    #[ORM\ManyToOne(inversedBy: 'facturations')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $client = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $totalFacture = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montantPaye = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $montantRemise = null;

    #[ORM\Column(length: 15)]
    private ?string $etat = null;

    #[ORM\Column(length: 15)]
    private ?string $etatLivraison = null;

    #[ORM\ManyToOne(inversedBy: 'facturations')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Caisse $caisse = null;

    #[ORM\ManyToOne(inversedBy: 'facturations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\ManyToOne(inversedBy: 'facturations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateFacturation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAlerte = null;

    #[ORM\OneToMany(mappedBy: 'facturation', targetEntity: FactureFraisSup::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $factureFraisSups;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomClientCash = null;

    #[ORM\OneToMany(mappedBy: 'facturation', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementCaisses;

    #[ORM\OneToMany(mappedBy: 'facturation', targetEntity: CommandeProduct::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $commandeProducts;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $fraisSup = null;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: MouvementCollaborateur::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementCollaborateurs;

    #[ORM\ManyToOne(inversedBy: 'facturations')]
    private ?ModePaiement $modePaie = null;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: CommissionVente::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $commissionVentes;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: ModificationFacture::class, orphanRemoval:true, cascade:['remove'])]
    private Collection $modificationFactures;

    #[ORM\ManyToOne(inversedBy: 'facturations')]
    private ?Proformat $proformat = null;


    public function __construct()
    {
        $this->factureFraisSups = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->commandeProducts = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->commissionVentes = new ArrayCollection();
        $this->modificationFactures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroFacture(): ?string
    {
        return $this->numeroFacture;
    }

    public function setNumeroFacture(string $numeroFacture): static
    {
        $this->numeroFacture = $numeroFacture;

        return $this;
    }

    public function getTotalFacture(): ?string
    {
        return $this->totalFacture;
    }

    public function setTotalFacture(?string $totalFacture): static
    {
        $this->totalFacture = $totalFacture;

        return $this;
    }

    public function getMontantPaye(): ?string
    {
        return $this->montantPaye;
    }

    public function setMontantPaye(?string $montantPaye): static
    {
        $this->montantPaye = $montantPaye;

        return $this;
    }

    public function getMontantRemise(): ?string
    {
        return $this->montantRemise;
    }

    public function setMontantRemise(string $montantRemise): static
    {
        $this->montantRemise = $montantRemise;

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

    public function getEtatLivraison(): ?string
    {
        return $this->etatLivraison;
    }

    public function setEtatLivraison(string $etatLivraison): static
    {
        $this->etatLivraison = $etatLivraison;

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

    public function getSaisiePar(): ?User
    {
        return $this->saisiePar;
    }

    public function setSaisiePar(?User $saisiePar): static
    {
        $this->saisiePar = $saisiePar;

        return $this;
    }

    public function getDateFacturation(): ?\DateTimeInterface
    {
        return $this->dateFacturation;
    }

    public function setDateFacturation(\DateTimeInterface $dateFacturation): static
    {
        $this->dateFacturation = $dateFacturation;

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

    public function getDateAlerte(): ?\DateTimeInterface
    {
        return $this->dateAlerte;
    }

    public function setDateAlerte(?\DateTimeInterface $dateAlerte): static
    {
        $this->dateAlerte = $dateAlerte;

        return $this;
    }

    /**
     * @return Collection<int, FactureFraisSup>
     */
    public function getFactureFraisSups(): Collection
    {
        return $this->factureFraisSups;
    }

    public function addFactureFraisSup(FactureFraisSup $factureFraisSup): static
    {
        if (!$this->factureFraisSups->contains($factureFraisSup)) {
            $this->factureFraisSups->add($factureFraisSup);
            $factureFraisSup->setFacturation($this);
        }

        return $this;
    }

    public function removeFactureFraisSup(FactureFraisSup $factureFraisSup): static
    {
        if ($this->factureFraisSups->removeElement($factureFraisSup)) {
            // set the owning side to null (unless already changed)
            if ($factureFraisSup->getFacturation() === $this) {
                $factureFraisSup->setFacturation(null);
            }
        }

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

    public function getNomClientCash(): ?string
    {
        return $this->nomClientCash;
    }

    public function setNomClientCash(?string $nomClientCash): static
    {
        $this->nomClientCash = $nomClientCash;

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
            $mouvementCaiss->setFacturation($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getFacturation() === $this) {
                $mouvementCaiss->setFacturation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommandeProduct>
     */
    public function getCommandeProducts(): Collection
    {
        return $this->commandeProducts;
    }

    public function addCommandeProduct(CommandeProduct $commandeProduct): static
    {
        if (!$this->commandeProducts->contains($commandeProduct)) {
            $this->commandeProducts->add($commandeProduct);
            $commandeProduct->setFacturation($this);
        }

        return $this;
    }

    public function removeCommandeProduct(CommandeProduct $commandeProduct): static
    {
        if ($this->commandeProducts->removeElement($commandeProduct)) {
            // set the owning side to null (unless already changed)
            if ($commandeProduct->getFacturation() === $this) {
                $commandeProduct->setFacturation(null);
            }
        }

        return $this;
    }

    public function getFraisSup(): ?string
    {
        return $this->fraisSup;
    }

    public function setFraisSup(?string $fraisSup): static
    {
        $this->fraisSup = $fraisSup;

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
            $mouvementCollaborateur->setFacture($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getFacture() === $this) {
                $mouvementCollaborateur->setFacture(null);
            }
        }

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

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, CommissionVente>
     */
    public function getCommissionVentes(): Collection
    {
        return $this->commissionVentes;
    }

    public function addCommissionVente(CommissionVente $commissionVente): static
    {
        if (!$this->commissionVentes->contains($commissionVente)) {
            $this->commissionVentes->add($commissionVente);
            $commissionVente->setFacture($this);
        }

        return $this;
    }

    public function removeCommissionVente(CommissionVente $commissionVente): static
    {
        if ($this->commissionVentes->removeElement($commissionVente)) {
            // set the owning side to null (unless already changed)
            if ($commissionVente->getFacture() === $this) {
                $commissionVente->setFacture(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ModificationFacture>
     */
    public function getModificationFactures(): Collection
    {
        return $this->modificationFactures;
    }

    public function addModificationFacture(ModificationFacture $modificationFacture): static
    {
        if (!$this->modificationFactures->contains($modificationFacture)) {
            $this->modificationFactures->add($modificationFacture);
            $modificationFacture->setFacture($this);
        }

        return $this;
    }

    public function removeModificationFacture(ModificationFacture $modificationFacture): static
    {
        if ($this->modificationFactures->removeElement($modificationFacture)) {
            // set the owning side to null (unless already changed)
            if ($modificationFacture->getFacture() === $this) {
                $modificationFacture->setFacture(null);
            }
        }

        return $this;
    }

    public function getProformat(): ?Proformat
    {
        return $this->proformat;
    }

    public function setProformat(?Proformat $proformat): static
    {
        $this->proformat = $proformat;

        return $this;
    }
   
    
}
