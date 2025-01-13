<?php

namespace App\Entity;

use App\Repository\DeviseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviseRepository::class)]
class Devise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15)]
    private ?string $nomDevise = null;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: AchatFournisseur::class)]
    private Collection $achatFournisseurs;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Decaissement::class)]
    private Collection $decaissements;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: MouvementCaisse::class)]
    private Collection $mouvementCaisses;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: MouvementCollaborateur::class)]
    private Collection $mouvementCollaborateurs;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: BonCommandeFournisseur::class)]
    private Collection $bonCommandeFournisseurs;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: ModifDecaissement::class)]
    private Collection $modifDecaissements;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Depenses::class)]
    private Collection $depenses;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Versement::class)]
    private Collection $versements;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Modification::class)]
    private Collection $modifications;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: TransfertFond::class)]
    private Collection $transfertFonds;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Recette::class)]
    private Collection $recettes;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: AjustementSolde::class)]
    private Collection $ajustementSoldes;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: FactureFraisSup::class)]
    private Collection $factureFraisSups;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: TauxDevise::class)]
    private Collection $tauxDevises;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: ProformatFraisSup::class)]
    private Collection $proformatFraisSups;

    #[ORM\OneToMany(mappedBy: 'deviseOrigine', targetEntity: EchangeDevise::class)]
    private Collection $echangeDevises;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: ClotureCaisse::class)]
    private Collection $clotureCaisses;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: FactureFrais::class)]
    private Collection $factureFrais;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: AchatFournisseurGeneral::class)]
    private Collection $achatFournisseurGenerals;

    public function __construct()
    {
        $this->achatFournisseurs = new ArrayCollection();
        $this->decaissements = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->bonCommandeFournisseurs = new ArrayCollection();
        $this->modifDecaissements = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->versements = new ArrayCollection();
        $this->modifications = new ArrayCollection();
        $this->transfertFonds = new ArrayCollection();
        $this->recettes = new ArrayCollection();
        $this->ajustementSoldes = new ArrayCollection();
        $this->factureFraisSups = new ArrayCollection();
        $this->tauxDevises = new ArrayCollection();
        $this->proformatFraisSups = new ArrayCollection();
        $this->echangeDevises = new ArrayCollection();
        $this->clotureCaisses = new ArrayCollection();
        $this->factureFrais = new ArrayCollection();
        $this->achatFournisseurGenerals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomDevise(): ?string
    {
        return $this->nomDevise;
    }

    public function setNomDevise(string $nomDevise): static
    {
        $this->nomDevise = $nomDevise;

        return $this;
    }

    /**
     * @return Collection<int, AchatFournisseur>
     */
    public function getAchatFournisseurs(): Collection
    {
        return $this->achatFournisseurs;
    }

    public function addAchatFournisseur(AchatFournisseur $achatFournisseur): static
    {
        if (!$this->achatFournisseurs->contains($achatFournisseur)) {
            $this->achatFournisseurs->add($achatFournisseur);
            $achatFournisseur->setDevise($this);
        }

        return $this;
    }

    public function removeAchatFournisseur(AchatFournisseur $achatFournisseur): static
    {
        if ($this->achatFournisseurs->removeElement($achatFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($achatFournisseur->getDevise() === $this) {
                $achatFournisseur->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Decaissement>
     */
    public function getDecaissements(): Collection
    {
        return $this->decaissements;
    }

    public function addDecaissement(Decaissement $decaissement): static
    {
        if (!$this->decaissements->contains($decaissement)) {
            $this->decaissements->add($decaissement);
            $decaissement->setDevise($this);
        }

        return $this;
    }

    public function removeDecaissement(Decaissement $decaissement): static
    {
        if ($this->decaissements->removeElement($decaissement)) {
            // set the owning side to null (unless already changed)
            if ($decaissement->getDevise() === $this) {
                $decaissement->setDevise(null);
            }
        }

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
            $mouvementCaiss->setDevise($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getDevise() === $this) {
                $mouvementCaiss->setDevise(null);
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
            $mouvementCollaborateur->setDevise($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getDevise() === $this) {
                $mouvementCollaborateur->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BonCommandeFournisseur>
     */
    public function getBonCommandeFournisseurs(): Collection
    {
        return $this->bonCommandeFournisseurs;
    }

    public function addBonCommandeFournisseur(BonCommandeFournisseur $bonCommandeFournisseur): static
    {
        if (!$this->bonCommandeFournisseurs->contains($bonCommandeFournisseur)) {
            $this->bonCommandeFournisseurs->add($bonCommandeFournisseur);
            $bonCommandeFournisseur->setDevise($this);
        }

        return $this;
    }

    public function removeBonCommandeFournisseur(BonCommandeFournisseur $bonCommandeFournisseur): static
    {
        if ($this->bonCommandeFournisseurs->removeElement($bonCommandeFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($bonCommandeFournisseur->getDevise() === $this) {
                $bonCommandeFournisseur->setDevise(null);
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
            $modifDecaissement->setDevise($this);
        }

        return $this;
    }

    public function removeModifDecaissement(ModifDecaissement $modifDecaissement): static
    {
        if ($this->modifDecaissements->removeElement($modifDecaissement)) {
            // set the owning side to null (unless already changed)
            if ($modifDecaissement->getDevise() === $this) {
                $modifDecaissement->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Depenses>
     */
    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depenses $depense): static
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses->add($depense);
            $depense->setDevise($this);
        }

        return $this;
    }

    public function removeDepense(Depenses $depense): static
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getDevise() === $this) {
                $depense->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Versement>
     */
    public function getVersements(): Collection
    {
        return $this->versements;
    }

    public function addVersement(Versement $versement): static
    {
        if (!$this->versements->contains($versement)) {
            $this->versements->add($versement);
            $versement->setDevise($this);
        }

        return $this;
    }

    public function removeVersement(Versement $versement): static
    {
        if ($this->versements->removeElement($versement)) {
            // set the owning side to null (unless already changed)
            if ($versement->getDevise() === $this) {
                $versement->setDevise(null);
            }
        }

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
            $modification->setDevise($this);
        }

        return $this;
    }

    public function removeModification(Modification $modification): static
    {
        if ($this->modifications->removeElement($modification)) {
            // set the owning side to null (unless already changed)
            if ($modification->getDevise() === $this) {
                $modification->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransfertFond>
     */
    public function getTransfertFonds(): Collection
    {
        return $this->transfertFonds;
    }

    public function addTransfertFond(TransfertFond $transfertFond): static
    {
        if (!$this->transfertFonds->contains($transfertFond)) {
            $this->transfertFonds->add($transfertFond);
            $transfertFond->setDevise($this);
        }

        return $this;
    }

    public function removeTransfertFond(TransfertFond $transfertFond): static
    {
        if ($this->transfertFonds->removeElement($transfertFond)) {
            // set the owning side to null (unless already changed)
            if ($transfertFond->getDevise() === $this) {
                $transfertFond->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recette>
     */
    public function getRecettes(): Collection
    {
        return $this->recettes;
    }

    public function addRecette(Recette $recette): static
    {
        if (!$this->recettes->contains($recette)) {
            $this->recettes->add($recette);
            $recette->setDevise($this);
        }

        return $this;
    }

    public function removeRecette(Recette $recette): static
    {
        if ($this->recettes->removeElement($recette)) {
            // set the owning side to null (unless already changed)
            if ($recette->getDevise() === $this) {
                $recette->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AjustementSolde>
     */
    public function getAjustementSoldes(): Collection
    {
        return $this->ajustementSoldes;
    }

    public function addAjustementSolde(AjustementSolde $ajustementSolde): static
    {
        if (!$this->ajustementSoldes->contains($ajustementSolde)) {
            $this->ajustementSoldes->add($ajustementSolde);
            $ajustementSolde->setDevise($this);
        }

        return $this;
    }

    public function removeAjustementSolde(AjustementSolde $ajustementSolde): static
    {
        if ($this->ajustementSoldes->removeElement($ajustementSolde)) {
            // set the owning side to null (unless already changed)
            if ($ajustementSolde->getDevise() === $this) {
                $ajustementSolde->setDevise(null);
            }
        }

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
            $factureFraisSup->setDevise($this);
        }

        return $this;
    }

    public function removeFactureFraisSup(FactureFraisSup $factureFraisSup): static
    {
        if ($this->factureFraisSups->removeElement($factureFraisSup)) {
            // set the owning side to null (unless already changed)
            if ($factureFraisSup->getDevise() === $this) {
                $factureFraisSup->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TauxDevise>
     */
    public function getTauxDevises(): Collection
    {
        return $this->tauxDevises;
    }

    public function addTauxDevise(TauxDevise $tauxDevise): static
    {
        if (!$this->tauxDevises->contains($tauxDevise)) {
            $this->tauxDevises->add($tauxDevise);
            $tauxDevise->setDevise($this);
        }

        return $this;
    }

    public function removeTauxDevise(TauxDevise $tauxDevise): static
    {
        if ($this->tauxDevises->removeElement($tauxDevise)) {
            // set the owning side to null (unless already changed)
            if ($tauxDevise->getDevise() === $this) {
                $tauxDevise->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProformatFraisSup>
     */
    public function getProformatFraisSups(): Collection
    {
        return $this->proformatFraisSups;
    }

    public function addProformatFraisSup(ProformatFraisSup $proformatFraisSup): static
    {
        if (!$this->proformatFraisSups->contains($proformatFraisSup)) {
            $this->proformatFraisSups->add($proformatFraisSup);
            $proformatFraisSup->setDevise($this);
        }

        return $this;
    }

    public function removeProformatFraisSup(ProformatFraisSup $proformatFraisSup): static
    {
        if ($this->proformatFraisSups->removeElement($proformatFraisSup)) {
            // set the owning side to null (unless already changed)
            if ($proformatFraisSup->getDevise() === $this) {
                $proformatFraisSup->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EchangeDevise>
     */
    public function getEchangeDevises(): Collection
    {
        return $this->echangeDevises;
    }

    public function addEchangeDevise(EchangeDevise $echangeDevise): static
    {
        if (!$this->echangeDevises->contains($echangeDevise)) {
            $this->echangeDevises->add($echangeDevise);
            $echangeDevise->setDeviseOrigine($this);
        }

        return $this;
    }

    public function removeEchangeDevise(EchangeDevise $echangeDevise): static
    {
        if ($this->echangeDevises->removeElement($echangeDevise)) {
            // set the owning side to null (unless already changed)
            if ($echangeDevise->getDeviseOrigine() === $this) {
                $echangeDevise->setDeviseOrigine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClotureCaisse>
     */
    public function getClotureCaisses(): Collection
    {
        return $this->clotureCaisses;
    }

    public function addClotureCaiss(ClotureCaisse $clotureCaiss): static
    {
        if (!$this->clotureCaisses->contains($clotureCaiss)) {
            $this->clotureCaisses->add($clotureCaiss);
            $clotureCaiss->setDevise($this);
        }

        return $this;
    }

    public function removeClotureCaiss(ClotureCaisse $clotureCaiss): static
    {
        if ($this->clotureCaisses->removeElement($clotureCaiss)) {
            // set the owning side to null (unless already changed)
            if ($clotureCaiss->getDevise() === $this) {
                $clotureCaiss->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FactureFrais>
     */
    public function getFactureFrais(): Collection
    {
        return $this->factureFrais;
    }

    public function addFactureFrai(FactureFrais $factureFrai): static
    {
        if (!$this->factureFrais->contains($factureFrai)) {
            $this->factureFrais->add($factureFrai);
            $factureFrai->setDevise($this);
        }

        return $this;
    }

    public function removeFactureFrai(FactureFrais $factureFrai): static
    {
        if ($this->factureFrais->removeElement($factureFrai)) {
            // set the owning side to null (unless already changed)
            if ($factureFrai->getDevise() === $this) {
                $factureFrai->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AchatFournisseurGeneral>
     */
    public function getAchatFournisseurGenerals(): Collection
    {
        return $this->achatFournisseurGenerals;
    }

    public function addAchatFournisseurGeneral(AchatFournisseurGeneral $achatFournisseurGeneral): static
    {
        if (!$this->achatFournisseurGenerals->contains($achatFournisseurGeneral)) {
            $this->achatFournisseurGenerals->add($achatFournisseurGeneral);
            $achatFournisseurGeneral->setDevise($this);
        }

        return $this;
    }

    public function removeAchatFournisseurGeneral(AchatFournisseurGeneral $achatFournisseurGeneral): static
    {
        if ($this->achatFournisseurGenerals->removeElement($achatFournisseurGeneral)) {
            // set the owning side to null (unless already changed)
            if ($achatFournisseurGeneral->getDevise() === $this) {
                $achatFournisseurGeneral->setDevise(null);
            }
        }

        return $this;
    }
}
