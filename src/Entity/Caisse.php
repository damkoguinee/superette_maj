<?php

namespace App\Entity;

use App\Repository\CaisseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaisseRepository::class)]
class Caisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $designation = null;

    #[ORM\ManyToOne(inversedBy: 'caisses')]
    private ?PointDeVente $pointDeVente = null;

    #[ORM\OneToMany(mappedBy: 'compteDecaisser', targetEntity: Decaissement::class)]
    private Collection $decaissements;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: MouvementCaisse::class)]
    private Collection $mouvementCaisses;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: MouvementCollaborateur::class)]
    private Collection $mouvementCollaborateurs;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: AvanceSalaire::class)]
    private Collection $avanceSalaires;

    #[ORM\OneToMany(mappedBy: 'caisseRetrait', targetEntity: Depenses::class)]
    private Collection $depenses;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: ModifDecaissement::class)]
    private Collection $modifDecaissements;

    #[ORM\OneToMany(mappedBy: 'Compte', targetEntity: Versement::class)]
    private Collection $versements;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: Modification::class)]
    private Collection $modifications;

    #[ORM\OneToMany(mappedBy: 'caisseDepart', targetEntity: TransfertFond::class)]
    private Collection $transfertFonds;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\OneToMany(mappedBy: 'caisseDepot', targetEntity: ChequeEspece::class)]
    private Collection $chequeEspeces;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: Recette::class)]
    private Collection $recettes;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: Facturation::class)]
    private Collection $facturations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroCompte = null;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: ModificationFacture::class)]
    private Collection $modificationFactures;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: SuppressionFacture::class)]
    private Collection $suppressionFactures;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: RetourProduct::class)]
    private Collection $retourProducts;

    #[ORM\OneToMany(mappedBy: 'caisseOrigine', targetEntity: EchangeDevise::class)]
    private Collection $echangeDevises;

    #[ORM\OneToMany(mappedBy: 'caisseDepart', targetEntity: GestionCheque::class)]
    private Collection $gestionCheques;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: ClotureCaisse::class)]
    private Collection $clotureCaisses;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: FactureFrais::class)]
    private Collection $factureFrais;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $document = null;

    public function __construct()
    {
        $this->decaissements = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->avanceSalaires = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->modifDecaissements = new ArrayCollection();
        $this->versements = new ArrayCollection();
        $this->modifications = new ArrayCollection();
        $this->transfertFonds = new ArrayCollection();
        $this->chequeEspeces = new ArrayCollection();
        $this->recettes = new ArrayCollection();
        $this->facturations = new ArrayCollection();
        $this->modificationFactures = new ArrayCollection();
        $this->suppressionFactures = new ArrayCollection();
        $this->retourProducts = new ArrayCollection();
        $this->echangeDevises = new ArrayCollection();
        $this->gestionCheques = new ArrayCollection();
        $this->clotureCaisses = new ArrayCollection();
        $this->factureFrais = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getPointDeVente(): ?PointDeVente
    {
        return $this->pointDeVente;
    }

    public function setPointDeVente(?PointDeVente $pointDeVente): static
    {
        $this->pointDeVente = $pointDeVente;

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
            $decaissement->setCompteDecaisser($this);
        }

        return $this;
    }

    public function removeDecaissement(Decaissement $decaissement): static
    {
        if ($this->decaissements->removeElement($decaissement)) {
            // set the owning side to null (unless already changed)
            if ($decaissement->getCompteDecaisser() === $this) {
                $decaissement->setCompteDecaisser(null);
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
            $mouvementCaiss->setCaisse($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getCaisse() === $this) {
                $mouvementCaiss->setCaisse(null);
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
            $mouvementCollaborateur->setCaisse($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getCaisse() === $this) {
                $mouvementCollaborateur->setCaisse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AvanceSalaire>
     */
    public function getAvanceSalaires(): Collection
    {
        return $this->avanceSalaires;
    }

    public function addAvanceSalaire(AvanceSalaire $avanceSalaire): static
    {
        if (!$this->avanceSalaires->contains($avanceSalaire)) {
            $this->avanceSalaires->add($avanceSalaire);
            $avanceSalaire->setCaisse($this);
        }

        return $this;
    }

    public function removeAvanceSalaire(AvanceSalaire $avanceSalaire): static
    {
        if ($this->avanceSalaires->removeElement($avanceSalaire)) {
            // set the owning side to null (unless already changed)
            if ($avanceSalaire->getCaisse() === $this) {
                $avanceSalaire->setCaisse(null);
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
            $depense->setCaisseRetrait($this);
        }

        return $this;
    }

    public function removeDepense(Depenses $depense): static
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getCaisseRetrait() === $this) {
                $depense->setCaisseRetrait(null);
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
            $modifDecaissement->setCaisse($this);
        }

        return $this;
    }

    public function removeModifDecaissement(ModifDecaissement $modifDecaissement): static
    {
        if ($this->modifDecaissements->removeElement($modifDecaissement)) {
            // set the owning side to null (unless already changed)
            if ($modifDecaissement->getCaisse() === $this) {
                $modifDecaissement->setCaisse(null);
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
            $versement->setCompte($this);
        }

        return $this;
    }

    public function removeVersement(Versement $versement): static
    {
        if ($this->versements->removeElement($versement)) {
            // set the owning side to null (unless already changed)
            if ($versement->getCompte() === $this) {
                $versement->setCompte(null);
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
            $modification->setCaisse($this);
        }

        return $this;
    }

    public function removeModification(Modification $modification): static
    {
        if ($this->modifications->removeElement($modification)) {
            // set the owning side to null (unless already changed)
            if ($modification->getCaisse() === $this) {
                $modification->setCaisse(null);
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
            $transfertFond->setCaisseDepart($this);
        }

        return $this;
    }

    public function removeTransfertFond(TransfertFond $transfertFond): static
    {
        if ($this->transfertFonds->removeElement($transfertFond)) {
            // set the owning side to null (unless already changed)
            if ($transfertFond->getCaisseDepart() === $this) {
                $transfertFond->setCaisseDepart(null);
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

    /**
     * @return Collection<int, ChequeEspece>
     */
    public function getChequeEspeces(): Collection
    {
        return $this->chequeEspeces;
    }

    public function addChequeEspece(ChequeEspece $chequeEspece): static
    {
        if (!$this->chequeEspeces->contains($chequeEspece)) {
            $this->chequeEspeces->add($chequeEspece);
            $chequeEspece->setCaisseDepot($this);
        }

        return $this;
    }

    public function removeChequeEspece(ChequeEspece $chequeEspece): static
    {
        if ($this->chequeEspeces->removeElement($chequeEspece)) {
            // set the owning side to null (unless already changed)
            if ($chequeEspece->getCaisseDepot() === $this) {
                $chequeEspece->setCaisseDepot(null);
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
            $recette->setCaisse($this);
        }

        return $this;
    }

    public function removeRecette(Recette $recette): static
    {
        if ($this->recettes->removeElement($recette)) {
            // set the owning side to null (unless already changed)
            if ($recette->getCaisse() === $this) {
                $recette->setCaisse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Facturation>
     */
    public function getFacturations(): Collection
    {
        return $this->facturations;
    }

    public function addFacturation(Facturation $facturation): static
    {
        if (!$this->facturations->contains($facturation)) {
            $this->facturations->add($facturation);
            $facturation->setCaisse($this);
        }

        return $this;
    }

    public function removeFacturation(Facturation $facturation): static
    {
        if ($this->facturations->removeElement($facturation)) {
            // set the owning side to null (unless already changed)
            if ($facturation->getCaisse() === $this) {
                $facturation->setCaisse(null);
            }
        }

        return $this;
    }

    public function getNumeroCompte(): ?string
    {
        return $this->numeroCompte;
    }

    public function setNumeroCompte(?string $numeroCompte): static
    {
        $this->numeroCompte = $numeroCompte;

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
            $modificationFacture->setCaisse($this);
        }

        return $this;
    }

    public function removeModificationFacture(ModificationFacture $modificationFacture): static
    {
        if ($this->modificationFactures->removeElement($modificationFacture)) {
            // set the owning side to null (unless already changed)
            if ($modificationFacture->getCaisse() === $this) {
                $modificationFacture->setCaisse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SuppressionFacture>
     */
    public function getSuppressionFactures(): Collection
    {
        return $this->suppressionFactures;
    }

    public function addSuppressionFacture(SuppressionFacture $suppressionFacture): static
    {
        if (!$this->suppressionFactures->contains($suppressionFacture)) {
            $this->suppressionFactures->add($suppressionFacture);
            $suppressionFacture->setCaisse($this);
        }

        return $this;
    }

    public function removeSuppressionFacture(SuppressionFacture $suppressionFacture): static
    {
        if ($this->suppressionFactures->removeElement($suppressionFacture)) {
            // set the owning side to null (unless already changed)
            if ($suppressionFacture->getCaisse() === $this) {
                $suppressionFacture->setCaisse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RetourProduct>
     */
    public function getRetourProducts(): Collection
    {
        return $this->retourProducts;
    }

    public function addRetourProduct(RetourProduct $retourProduct): static
    {
        if (!$this->retourProducts->contains($retourProduct)) {
            $this->retourProducts->add($retourProduct);
            $retourProduct->setCaisse($this);
        }

        return $this;
    }

    public function removeRetourProduct(RetourProduct $retourProduct): static
    {
        if ($this->retourProducts->removeElement($retourProduct)) {
            // set the owning side to null (unless already changed)
            if ($retourProduct->getCaisse() === $this) {
                $retourProduct->setCaisse(null);
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
            $echangeDevise->setCaisseOrigine($this);
        }

        return $this;
    }

    public function removeEchangeDevise(EchangeDevise $echangeDevise): static
    {
        if ($this->echangeDevises->removeElement($echangeDevise)) {
            // set the owning side to null (unless already changed)
            if ($echangeDevise->getCaisseOrigine() === $this) {
                $echangeDevise->setCaisseOrigine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GestionCheque>
     */
    public function getGestionCheques(): Collection
    {
        return $this->gestionCheques;
    }

    public function addGestionCheque(GestionCheque $gestionCheque): static
    {
        if (!$this->gestionCheques->contains($gestionCheque)) {
            $this->gestionCheques->add($gestionCheque);
            $gestionCheque->setCaisseDepart($this);
        }

        return $this;
    }

    public function removeGestionCheque(GestionCheque $gestionCheque): static
    {
        if ($this->gestionCheques->removeElement($gestionCheque)) {
            // set the owning side to null (unless already changed)
            if ($gestionCheque->getCaisseDepart() === $this) {
                $gestionCheque->setCaisseDepart(null);
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
            $clotureCaiss->setCaisse($this);
        }

        return $this;
    }

    public function removeClotureCaiss(ClotureCaisse $clotureCaiss): static
    {
        if ($this->clotureCaisses->removeElement($clotureCaiss)) {
            // set the owning side to null (unless already changed)
            if ($clotureCaiss->getCaisse() === $this) {
                $clotureCaiss->setCaisse(null);
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
            $factureFrai->setCaisse($this);
        }

        return $this;
    }

    public function removeFactureFrai(FactureFrais $factureFrai): static
    {
        if ($this->factureFrais->removeElement($factureFrai)) {
            // set the owning side to null (unless already changed)
            if ($factureFrai->getCaisse() === $this) {
                $factureFrai->setCaisse(null);
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
}
