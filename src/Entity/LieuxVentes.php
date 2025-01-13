<?php

namespace App\Entity;

use App\Repository\LieuxVentesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LieuxVentesRepository::class)]
class LieuxVentes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lieuxVentes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'lieuxVentes')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $gestionnaire = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 100)]
    private ?string $pays = null;

    #[ORM\Column(length: 100)]
    private ?string $region = null;

    #[ORM\Column(length: 100)]
    private ?string $ville = null;

    #[ORM\Column(length: 100)]
    private ?string $telephone = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $initial = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeCommerce = null;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: ListeStock::class)]
    private Collection $listeStocks;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: User::class)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: ListeInventaire::class)]
    private Collection $listeInventaires;

    #[ORM\OneToMany(mappedBy: 'rattachement', targetEntity: Client::class)]
    private Collection $clients;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: MouvementProduct::class)]
    private Collection $mouvementProducts;

    #[ORM\OneToMany(mappedBy: 'lieuVenteDepart', targetEntity: TransfertProducts::class)]
    private Collection $transfertProduct;

    #[ORM\OneToMany(mappedBy: 'lieuVenteRecep', targetEntity: TransfertProducts::class)]
    private Collection $transfertProductRecep;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: AchatFournisseur::class)]
    private Collection $achatFournisseurs;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: PointDeVente::class)]
    private Collection $pointDeVentes;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: Decaissement::class)]
    private Collection $decaissements;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: MouvementCaisse::class)]
    private Collection $mouvementCaisses;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: MouvementCollaborateur::class)]
    private Collection $mouvementCollaborateurs;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: BonCommandeFournisseur::class)]
    private Collection $bonCommandeFournisseurs;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: AbsencesPersonnels::class)]
    private Collection $absencesPersonnels;

    #[ORM\Column(length: 150)]
    private ?string $lieu = null;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: PrimesPersonnel::class)]
    private Collection $primesPersonnels;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: AvanceSalaire::class)]
    private Collection $avanceSalaires;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: PaiementSalairePersonnel::class)]
    private Collection $paiementSalairePersonnels;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: Depenses::class)]
    private Collection $depenses;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: Versement::class)]
    private Collection $versements;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: TransfertFond::class)]
    private Collection $transfertFonds;

    #[ORM\OneToMany(mappedBy: 'lieuVenteReception', targetEntity: TransfertFond::class)]
    private Collection $transferyFondsRecep;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: ChequeEspece::class)]
    private Collection $chequeEspeces;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: AjustementSolde::class)]
    private Collection $ajustementSoldes;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: Recette::class)]
    private Collection $recettes;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: Facturation::class)]
    private Collection $facturations;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: TauxDevise::class)]
    private Collection $tauxDevises;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: ModificationFacture::class)]
    private Collection $modificationFactures;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: Proformat::class)]
    private Collection $proformats;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: SuppressionFacture::class)]
    private Collection $suppressionFactures;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: EchangeDevise::class)]
    private Collection $echangeDevises;

    #[ORM\OneToMany(mappedBy: 'lieuVenteDepart', targetEntity: GestionCheque::class)]
    private Collection $gestionCheques;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: ClotureCaisse::class)]
    private Collection $clotureCaisses;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: RetourProductFournisseur::class)]
    private Collection $retourProductFournisseurs;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: FactureFrais::class)]
    private Collection $factureFrais;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: AchatFournisseurGeneral::class)]
    private Collection $achatFournisseurGenerals;

    #[ORM\OneToMany(mappedBy: 'lieuVente', targetEntity: Promotion::class)]
    private Collection $promotions;

    public function __construct()
    {
        $this->listeStocks = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->listeInventaires = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->mouvementProducts = new ArrayCollection();
        $this->transfertProduct = new ArrayCollection();
        $this->transfertProductRecep = new ArrayCollection();
        $this->achatFournisseurs = new ArrayCollection();
        $this->pointDeVentes = new ArrayCollection();
        $this->decaissements = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->bonCommandeFournisseurs = new ArrayCollection();
        $this->absencesPersonnels = new ArrayCollection();
        $this->primesPersonnels = new ArrayCollection();
        $this->avanceSalaires = new ArrayCollection();
        $this->paiementSalairePersonnels = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->versements = new ArrayCollection();
        $this->transfertFonds = new ArrayCollection();
        $this->transferyFondsRecep = new ArrayCollection();
        $this->chequeEspeces = new ArrayCollection();
        $this->ajustementSoldes = new ArrayCollection();
        $this->recettes = new ArrayCollection();
        $this->facturations = new ArrayCollection();
        $this->tauxDevises = new ArrayCollection();
        $this->modificationFactures = new ArrayCollection();
        $this->proformats = new ArrayCollection();
        $this->echangeDevises = new ArrayCollection();
        $this->gestionCheques = new ArrayCollection();
        $this->clotureCaisses = new ArrayCollection();
        $this->retourProductFournisseurs = new ArrayCollection();
        $this->factureFrais = new ArrayCollection();
        $this->achatFournisseurGenerals = new ArrayCollection();
        $this->promotions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getGestionnaire(): ?User
    {
        return $this->gestionnaire;
    }

    public function setGestionnaire(?User $gestionnaire): static
    {
        $this->gestionnaire = $gestionnaire;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getInitial(): ?string
    {
        return $this->initial;
    }

    public function setInitial(?string $initial): static
    {
        $this->initial = $initial;

        return $this;
    }

    public function getTypeCommerce(): ?string
    {
        return $this->typeCommerce;
    }

    public function setTypeCommerce(?string $typeCommerce): static
    {
        $this->typeCommerce = $typeCommerce;

        return $this;
    }

    /**
     * @return Collection<int, ListeStock>
     */
    public function getListeStocks(): Collection
    {
        return $this->listeStocks;
    }

    public function addListeStock(ListeStock $listeStock): static
    {
        if (!$this->listeStocks->contains($listeStock)) {
            $this->listeStocks->add($listeStock);
            $listeStock->setLieuVente($this);
        }

        return $this;
    }

    public function removeListeStock(ListeStock $listeStock): static
    {
        if ($this->listeStocks->removeElement($listeStock)) {
            // set the owning side to null (unless already changed)
            if ($listeStock->getLieuVente() === $this) {
                $listeStock->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setLieuVente($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getLieuVente() === $this) {
                $user->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeInventaire>
     */
    public function getListeInventaires(): Collection
    {
        return $this->listeInventaires;
    }

    public function addListeInventaire(ListeInventaire $listeInventaire): static
    {
        if (!$this->listeInventaires->contains($listeInventaire)) {
            $this->listeInventaires->add($listeInventaire);
            $listeInventaire->setLieuVente($this);
        }

        return $this;
    }

    public function removeListeInventaire(ListeInventaire $listeInventaire): static
    {
        if ($this->listeInventaires->removeElement($listeInventaire)) {
            // set the owning side to null (unless already changed)
            if ($listeInventaire->getLieuVente() === $this) {
                $listeInventaire->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setRattachement($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getRattachement() === $this) {
                $client->setRattachement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MouvementProduct>
     */
    public function getMouvementProducts(): Collection
    {
        return $this->mouvementProducts;
    }

    public function addMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if (!$this->mouvementProducts->contains($mouvementProduct)) {
            $this->mouvementProducts->add($mouvementProduct);
            $mouvementProduct->setLieuVente($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getLieuVente() === $this) {
                $mouvementProduct->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransfertProducts>
     */
    public function getTransfertProduct(): Collection
    {
        return $this->transfertProduct;
    }

    public function addTransfertProduct(TransfertProducts $transfertProduct): static
    {
        if (!$this->transfertProduct->contains($transfertProduct)) {
            $this->transfertProduct->add($transfertProduct);
            $transfertProduct->setLieuVenteDepart($this);
        }

        return $this;
    }

    public function removeTransfertProduct(TransfertProducts $transfertProduct): static
    {
        if ($this->transfertProduct->removeElement($transfertProduct)) {
            // set the owning side to null (unless already changed)
            if ($transfertProduct->getLieuVenteDepart() === $this) {
                $transfertProduct->setLieuVenteDepart(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransfertProducts>
     */
    public function getTransfertProductRecep(): Collection
    {
        return $this->transfertProductRecep;
    }

    public function addTransfertProductRecep(TransfertProducts $transfertProductRecep): static
    {
        if (!$this->transfertProductRecep->contains($transfertProductRecep)) {
            $this->transfertProductRecep->add($transfertProductRecep);
            $transfertProductRecep->setLieuVenteRecep($this);
        }

        return $this;
    }

    public function removeTransfertProductRecep(TransfertProducts $transfertProductRecep): static
    {
        if ($this->transfertProductRecep->removeElement($transfertProductRecep)) {
            // set the owning side to null (unless already changed)
            if ($transfertProductRecep->getLieuVenteRecep() === $this) {
                $transfertProductRecep->setLieuVenteRecep(null);
            }
        }

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
            $achatFournisseur->setLieuVente($this);
        }

        return $this;
    }

    public function removeAchatFournisseur(AchatFournisseur $achatFournisseur): static
    {
        if ($this->achatFournisseurs->removeElement($achatFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($achatFournisseur->getLieuVente() === $this) {
                $achatFournisseur->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PointDeVente>
     */
    public function getPointDeVentes(): Collection
    {
        return $this->pointDeVentes;
    }

    public function addPointDeVente(PointDeVente $pointDeVente): static
    {
        if (!$this->pointDeVentes->contains($pointDeVente)) {
            $this->pointDeVentes->add($pointDeVente);
            $pointDeVente->setLieuVente($this);
        }

        return $this;
    }

    public function removePointDeVente(PointDeVente $pointDeVente): static
    {
        if ($this->pointDeVentes->removeElement($pointDeVente)) {
            // set the owning side to null (unless already changed)
            if ($pointDeVente->getLieuVente() === $this) {
                $pointDeVente->setLieuVente(null);
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
            $decaissement->setLieuVente($this);
        }

        return $this;
    }

    public function removeDecaissement(Decaissement $decaissement): static
    {
        if ($this->decaissements->removeElement($decaissement)) {
            // set the owning side to null (unless already changed)
            if ($decaissement->getLieuVente() === $this) {
                $decaissement->setLieuVente(null);
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
            $mouvementCaiss->setLieuVente($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getLieuVente() === $this) {
                $mouvementCaiss->setLieuVente(null);
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
            $mouvementCollaborateur->setLieuVente($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getLieuVente() === $this) {
                $mouvementCollaborateur->setLieuVente(null);
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
            $bonCommandeFournisseur->setLieuVente($this);
        }

        return $this;
    }

    public function removeBonCommandeFournisseur(BonCommandeFournisseur $bonCommandeFournisseur): static
    {
        if ($this->bonCommandeFournisseurs->removeElement($bonCommandeFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($bonCommandeFournisseur->getLieuVente() === $this) {
                $bonCommandeFournisseur->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AbsencesPersonnels>
     */
    public function getAbsencesPersonnels(): Collection
    {
        return $this->absencesPersonnels;
    }

    public function addAbsencesPersonnel(AbsencesPersonnels $absencesPersonnel): static
    {
        if (!$this->absencesPersonnels->contains($absencesPersonnel)) {
            $this->absencesPersonnels->add($absencesPersonnel);
            $absencesPersonnel->setLieuVente($this);
        }

        return $this;
    }

    public function removeAbsencesPersonnel(AbsencesPersonnels $absencesPersonnel): static
    {
        if ($this->absencesPersonnels->removeElement($absencesPersonnel)) {
            // set the owning side to null (unless already changed)
            if ($absencesPersonnel->getLieuVente() === $this) {
                $absencesPersonnel->setLieuVente(null);
            }
        }

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * @return Collection<int, PrimesPersonnel>
     */
    public function getPrimesPersonnels(): Collection
    {
        return $this->primesPersonnels;
    }

    public function addPrimesPersonnel(PrimesPersonnel $primesPersonnel): static
    {
        if (!$this->primesPersonnels->contains($primesPersonnel)) {
            $this->primesPersonnels->add($primesPersonnel);
            $primesPersonnel->setLieuVente($this);
        }

        return $this;
    }

    public function removePrimesPersonnel(PrimesPersonnel $primesPersonnel): static
    {
        if ($this->primesPersonnels->removeElement($primesPersonnel)) {
            // set the owning side to null (unless already changed)
            if ($primesPersonnel->getLieuVente() === $this) {
                $primesPersonnel->setLieuVente(null);
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
            $avanceSalaire->setLieuVente($this);
        }

        return $this;
    }

    public function removeAvanceSalaire(AvanceSalaire $avanceSalaire): static
    {
        if ($this->avanceSalaires->removeElement($avanceSalaire)) {
            // set the owning side to null (unless already changed)
            if ($avanceSalaire->getLieuVente() === $this) {
                $avanceSalaire->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaiementSalairePersonnel>
     */
    public function getPaiementSalairePersonnels(): Collection
    {
        return $this->paiementSalairePersonnels;
    }

    public function addPaiementSalairePersonnel(PaiementSalairePersonnel $paiementSalairePersonnel): static
    {
        if (!$this->paiementSalairePersonnels->contains($paiementSalairePersonnel)) {
            $this->paiementSalairePersonnels->add($paiementSalairePersonnel);
            $paiementSalairePersonnel->setLieuVente($this);
        }

        return $this;
    }

    public function removePaiementSalairePersonnel(PaiementSalairePersonnel $paiementSalairePersonnel): static
    {
        if ($this->paiementSalairePersonnels->removeElement($paiementSalairePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($paiementSalairePersonnel->getLieuVente() === $this) {
                $paiementSalairePersonnel->setLieuVente(null);
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
            $depense->setLieuVente($this);
        }

        return $this;
    }

    public function removeDepense(Depenses $depense): static
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getLieuVente() === $this) {
                $depense->setLieuVente(null);
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
            $versement->setLieuVente($this);
        }

        return $this;
    }

    public function removeVersement(Versement $versement): static
    {
        if ($this->versements->removeElement($versement)) {
            // set the owning side to null (unless already changed)
            if ($versement->getLieuVente() === $this) {
                $versement->setLieuVente(null);
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
            $transfertFond->setLieuVente($this);
        }

        return $this;
    }

    public function removeTransfertFond(TransfertFond $transfertFond): static
    {
        if ($this->transfertFonds->removeElement($transfertFond)) {
            // set the owning side to null (unless already changed)
            if ($transfertFond->getLieuVente() === $this) {
                $transfertFond->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransfertFond>
     */
    public function getTransferyFondsRecep(): Collection
    {
        return $this->transferyFondsRecep;
    }

    public function addTransferyFondsRecep(TransfertFond $transferyFondsRecep): static
    {
        if (!$this->transferyFondsRecep->contains($transferyFondsRecep)) {
            $this->transferyFondsRecep->add($transferyFondsRecep);
            $transferyFondsRecep->setLieuVenteReception($this);
        }

        return $this;
    }

    public function removeTransferyFondsRecep(TransfertFond $transferyFondsRecep): static
    {
        if ($this->transferyFondsRecep->removeElement($transferyFondsRecep)) {
            // set the owning side to null (unless already changed)
            if ($transferyFondsRecep->getLieuVenteReception() === $this) {
                $transferyFondsRecep->setLieuVenteReception(null);
            }
        }

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
            $chequeEspece->setLieuVente($this);
        }

        return $this;
    }

    public function removeChequeEspece(ChequeEspece $chequeEspece): static
    {
        if ($this->chequeEspeces->removeElement($chequeEspece)) {
            // set the owning side to null (unless already changed)
            if ($chequeEspece->getLieuVente() === $this) {
                $chequeEspece->setLieuVente(null);
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
            $ajustementSolde->setLieuVente($this);
        }

        return $this;
    }

    public function removeAjustementSolde(AjustementSolde $ajustementSolde): static
    {
        if ($this->ajustementSoldes->removeElement($ajustementSolde)) {
            // set the owning side to null (unless already changed)
            if ($ajustementSolde->getLieuVente() === $this) {
                $ajustementSolde->setLieuVente(null);
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
            $recette->setLieuVente($this);
        }

        return $this;
    }

    public function removeRecette(Recette $recette): static
    {
        if ($this->recettes->removeElement($recette)) {
            // set the owning side to null (unless already changed)
            if ($recette->getLieuVente() === $this) {
                $recette->setLieuVente(null);
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
            $facturation->setLieuVente($this);
        }

        return $this;
    }

    public function removeFacturation(Facturation $facturation): static
    {
        if ($this->facturations->removeElement($facturation)) {
            // set the owning side to null (unless already changed)
            if ($facturation->getLieuVente() === $this) {
                $facturation->setLieuVente(null);
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
            $tauxDevise->setLieuVente($this);
        }

        return $this;
    }

    public function removeTauxDevise(TauxDevise $tauxDevise): static
    {
        if ($this->tauxDevises->removeElement($tauxDevise)) {
            // set the owning side to null (unless already changed)
            if ($tauxDevise->getLieuVente() === $this) {
                $tauxDevise->setLieuVente(null);
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
            $modificationFacture->setLieuVente($this);
        }

        return $this;
    }

    public function removeModificationFacture(ModificationFacture $modificationFacture): static
    {
        if ($this->modificationFactures->removeElement($modificationFacture)) {
            // set the owning side to null (unless already changed)
            if ($modificationFacture->getLieuVente() === $this) {
                $modificationFacture->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Proformat>
     */
    public function getProformats(): Collection
    {
        return $this->proformats;
    }

    public function addProformat(Proformat $proformat): static
    {
        if (!$this->proformats->contains($proformat)) {
            $this->proformats->add($proformat);
            $proformat->setLieuVente($this);
        }

        return $this;
    }

    public function removeProformat(Proformat $proformat): static
    {
        if ($this->proformats->removeElement($proformat)) {
            // set the owning side to null (unless already changed)
            if ($proformat->getLieuVente() === $this) {
                $proformat->setLieuVente(null);
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
            $suppressionFacture->setLieuVente($this);
        }

        return $this;
    }

    public function removeSuppressionFacture(SuppressionFacture $suppressionFacture): static
    {
        if ($this->suppressionFactures->removeElement($suppressionFacture)) {
            // set the owning side to null (unless already changed)
            if ($suppressionFacture->getLieuVente() === $this) {
                $suppressionFacture->setLieuVente(null);
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
            $echangeDevise->setLieuVente($this);
        }

        return $this;
    }

    public function removeEchangeDevise(EchangeDevise $echangeDevise): static
    {
        if ($this->echangeDevises->removeElement($echangeDevise)) {
            // set the owning side to null (unless already changed)
            if ($echangeDevise->getLieuVente() === $this) {
                $echangeDevise->setLieuVente(null);
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
            $gestionCheque->setLieuVenteDepart($this);
        }

        return $this;
    }

    public function removeGestionCheque(GestionCheque $gestionCheque): static
    {
        if ($this->gestionCheques->removeElement($gestionCheque)) {
            // set the owning side to null (unless already changed)
            if ($gestionCheque->getLieuVenteDepart() === $this) {
                $gestionCheque->setLieuVenteDepart(null);
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
            $clotureCaiss->setLieuVente($this);
        }

        return $this;
    }

    public function removeClotureCaiss(ClotureCaisse $clotureCaiss): static
    {
        if ($this->clotureCaisses->removeElement($clotureCaiss)) {
            // set the owning side to null (unless already changed)
            if ($clotureCaiss->getLieuVente() === $this) {
                $clotureCaiss->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RetourProductFournisseur>
     */
    public function getRetourProductFournisseurs(): Collection
    {
        return $this->retourProductFournisseurs;
    }

    public function addRetourProductFournisseur(RetourProductFournisseur $retourProductFournisseur): static
    {
        if (!$this->retourProductFournisseurs->contains($retourProductFournisseur)) {
            $this->retourProductFournisseurs->add($retourProductFournisseur);
            $retourProductFournisseur->setLieuVente($this);
        }

        return $this;
    }

    public function removeRetourProductFournisseur(RetourProductFournisseur $retourProductFournisseur): static
    {
        if ($this->retourProductFournisseurs->removeElement($retourProductFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($retourProductFournisseur->getLieuVente() === $this) {
                $retourProductFournisseur->setLieuVente(null);
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
            $factureFrai->setLieuVente($this);
        }

        return $this;
    }

    public function removeFactureFrai(FactureFrais $factureFrai): static
    {
        if ($this->factureFrais->removeElement($factureFrai)) {
            // set the owning side to null (unless already changed)
            if ($factureFrai->getLieuVente() === $this) {
                $factureFrai->setLieuVente(null);
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
            $achatFournisseurGeneral->setLieuVente($this);
        }

        return $this;
    }

    public function removeAchatFournisseurGeneral(AchatFournisseurGeneral $achatFournisseurGeneral): static
    {
        if ($this->achatFournisseurGenerals->removeElement($achatFournisseurGeneral)) {
            // set the owning side to null (unless already changed)
            if ($achatFournisseurGeneral->getLieuVente() === $this) {
                $achatFournisseurGeneral->setLieuVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Promotion>
     */
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): static
    {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions->add($promotion);
            $promotion->setLieuVente($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): static
    {
        if ($this->promotions->removeElement($promotion)) {
            // set the owning side to null (unless already changed)
            if ($promotion->getLieuVente() === $this) {
                $promotion->setLieuVente(null);
            }
        }

        return $this;
    }

    

   
}
