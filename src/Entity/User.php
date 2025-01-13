<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(nullable: true)]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 150)]
    private ?string $prenom = null;

    #[ORM\OneToMany(mappedBy: 'gestionnaire', targetEntity: LieuxVentes::class)]
    private Collection $lieuxVentes;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'responsable', targetEntity: ListeStock::class)]
    private Collection $listeStocks;

    #[ORM\Column(length: 50, nullable: false)]
    private ?string $typeUser = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Personnel::class, orphanRemoval:true, cascade: ['persist', 'remove'])]
    private Collection $personnels;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Client::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $clients;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $adresse = null;

    #[ORM\Column(length: 100, nullable: false)]
    private ?string $ville = null;

    #[ORM\Column(length: 100, nullable: false)]
    private ?string $pays = null;

    #[ORM\Column(length: 10)]
    private ?string $statut = null;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: ListeInventaire::class, orphanRemoval:true, cascade: ['persist', 'remove'])]
    private Collection $listeInventaires;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: Inventaire::class, orphanRemoval:true, cascade: ['persist', 'remove'])]
    private Collection $inventaires;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: MouvementProduct::class )]
    private Collection $mouvementProducts;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: MouvementProduct::class)]
    private Collection $mouvementProductsClient;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: AnomalieProduit::class)]
    private Collection $anomalieProduits;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: ListeTransfertProduct::class)]
    private Collection $listeTransfertProduct;

    #[ORM\OneToMany(mappedBy: 'creePar', targetEntity: TransfertProducts::class)]
    private Collection $transfertProduct;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: AchatFournisseur::class)]
    private Collection $achatFournisseurs;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: ListeProductAchatFournisseur::class)]
    private Collection $listeProductAchatFournisseurs;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: AchatFournisseur::class)]
    private Collection $achat;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Decaissement::class)]
    private Collection $decaissements;

    #[ORM\OneToMany(mappedBy: 'traitePar', targetEntity: Decaissement::class)]
    private Collection $decaissementPersonnel;

    #[ORM\OneToMany(mappedBy: 'collaborateur', targetEntity: MouvementCollaborateur::class, orphanRemoval:true, cascade: ['persist', 'remove'])]
    private Collection $mouvementCollaborateurs;

    #[ORM\OneToMany(mappedBy: 'traitePar', targetEntity: MouvementCollaborateur::class)]
    private Collection $mouvementPersonnels;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: BonCommandeFournisseur::class)]
    private Collection $bonCommandeFournisseurs;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: BonCommandeFournisseur::class)]
    private Collection $bonCommandeFournisseursPersonnel;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: AbsencesPersonnels::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $absencesPersonnels;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: PrimesPersonnel::class)]
    private Collection $primesPersonnels;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: AbsencesPersonnels::class)]
    private Collection $absencesPersonnelsSaisie;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AvanceSalaire::class)]
    private Collection $avanceSalaires;

    #[ORM\OneToMany(mappedBy: 'traitePar', targetEntity: AvanceSalaire::class)]
    private Collection $avanceSalaireTraite;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: PaiementSalairePersonnel::class)]
    private Collection $paiementSalairePersonnels;

    #[ORM\OneToMany(mappedBy: 'traitePar', targetEntity: ModifDecaissement::class)]
    private Collection $modifDecaissements;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: ModifDecaissement::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $modifDecClients;

    #[ORM\OneToMany(mappedBy: 'traitePar', targetEntity: Depenses::class)]
    private Collection $depenses;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Versement::class)]
    private Collection $versements;

    #[ORM\OneToMany(mappedBy: 'traitePar', targetEntity: Versement::class)]
    private Collection $versementTraite;

    #[ORM\OneToMany(mappedBy: 'collaborateur', targetEntity: Modification::class)]
    private Collection $modifications;

    #[ORM\OneToMany(mappedBy: 'traitePar', targetEntity: TransfertFond::class)]
    private Collection $transfertFonds;

    #[ORM\OneToMany(mappedBy: 'collaborateur', targetEntity: ChequeEspece::class)]
    private Collection $chequeEspeces;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: Recette::class)]
    private Collection $recettes;

    #[ORM\OneToMany(mappedBy: 'collaborateur', targetEntity: AjustementSolde::class, cascade: ['remove'])]
    private Collection $ajustementSoldes;

    #[ORM\OneToMany(mappedBy: 'traitePar', targetEntity: AjustementSolde::class, orphanRemoval:true, cascade: ['persist', 'remove'])]
    private Collection $ajustementSoldeTraite;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Region $region = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: Livraison::class)]
    private Collection $livraisons;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Facturation::class)]
    private Collection $facturations;

    #[ORM\OneToMany(mappedBy: 'beneficiaire', targetEntity: CommissionVente::class)]
    private Collection $commissionVentes;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: ModificationFacture::class)]
    private Collection $modificationFactures;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Proformat::class)]
    private Collection $proformats;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: SuppressionFacture::class)]
    private Collection $suppressionFacturesClient;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: SuppressionFacture::class)]
    private Collection $suppressionFactures;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: RetourProduct::class)]
    private Collection $retourProducts;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: EchangeDevise::class)]
    private Collection $echangeDevises;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: MouvementCaisse::class)]
    private Collection $mouvementCaisses;

    #[ORM\OneToMany(mappedBy: 'traitePar', targetEntity: GestionCheque::class)]
    private Collection $gestionCheques;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: ClotureCaisse::class)]
    private Collection $clotureCaisses;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: RetourProductFournisseur::class)]
    private Collection $retourProductFournisseurs;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: FactureFrais::class)]
    private Collection $factureFrais;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: ListeProductAchatFournisseurFrais::class)]
    private Collection $listeProductAchatFournisseurFrais;

    #[ORM\OneToMany(mappedBy: 'personnel', targetEntity: ListeProductAchatFournisseurMultiple::class)]
    private Collection $listeProductAchatFournisseurMultiples;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: AchatFournisseurGeneral::class)]
    private Collection $achatFournisseurGenerals;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: Promotion::class)]
    private Collection $promotions;

    #[ORM\OneToMany(mappedBy: 'saisiePar', targetEntity: SortieStock::class)]
    private Collection $sortieStocks;
    public function __construct()
    {
        $this->lieuxVentes = new ArrayCollection();
        $this->listeStocks = new ArrayCollection();
        $this->personnels = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->listeInventaires = new ArrayCollection();
        $this->inventaires = new ArrayCollection();
        $this->mouvementProducts = new ArrayCollection();
        $this->mouvementProductsClient = new ArrayCollection();
        $this->anomalieProduits = new ArrayCollection();
        $this->listeTransfertProduct = new ArrayCollection();
        $this->transfertProduct = new ArrayCollection();
        $this->achatFournisseurs = new ArrayCollection();
        $this->listeProductAchatFournisseurs = new ArrayCollection();
        $this->achat = new ArrayCollection();
        $this->decaissements = new ArrayCollection();
        $this->decaissementPersonnel = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->mouvementPersonnels = new ArrayCollection();
        $this->bonCommandeFournisseurs = new ArrayCollection();
        $this->bonCommandeFournisseursPersonnel = new ArrayCollection();
        $this->absencesPersonnels = new ArrayCollection();
        $this->absencesPersonnelsSaisie = new ArrayCollection();
        $this->primesPersonnels = new ArrayCollection();
        $this->avanceSalaires = new ArrayCollection();
        $this->avanceSalaireTraite = new ArrayCollection();
        $this->paiementSalairePersonnels = new ArrayCollection();
        $this->modifDecaissements = new ArrayCollection();
        $this->modifDecClients = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->versements = new ArrayCollection();
        $this->versementTraite = new ArrayCollection();
        $this->modifications = new ArrayCollection();
        $this->transfertFonds = new ArrayCollection();
        $this->chequeEspeces = new ArrayCollection();
        $this->recettes = new ArrayCollection();
        $this->ajustementSoldes = new ArrayCollection();
        $this->ajustementSoldeTraite = new ArrayCollection();
        $this->livraisons = new ArrayCollection();
        $this->facturations = new ArrayCollection();
        $this->commissionVentes = new ArrayCollection();
        $this->modificationFactures = new ArrayCollection();
        $this->proformats = new ArrayCollection();
        $this->suppressionFacturesClient = new ArrayCollection();
        $this->suppressionFactures = new ArrayCollection();
        $this->retourProducts = new ArrayCollection();
        $this->echangeDevises = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->gestionCheques = new ArrayCollection();
        $this->clotureCaisses = new ArrayCollection();
        $this->retourProductFournisseurs = new ArrayCollection();
        $this->factureFrais = new ArrayCollection();
        $this->listeProductAchatFournisseurFrais = new ArrayCollection();
        $this->listeProductAchatFournisseurMultiples = new ArrayCollection();
        $this->achatFournisseurGenerals = new ArrayCollection();
        $this->promotions = new ArrayCollection();
        $this->sortieStocks = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        // Vérifie si le mot de passe est null, si c'est le cas, retourne une chaîne vide
        if ($this->password === null) {
            return '';
        }
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return Collection<int, LieuxVentes>
     */
    public function getLieuxVentes(): Collection
    {
        return $this->lieuxVentes;
    }

    public function addLieuxVente(LieuxVentes $lieuxVente): static
    {
        if (!$this->lieuxVentes->contains($lieuxVente)) {
            $this->lieuxVentes->add($lieuxVente);
            $lieuxVente->setGestionnaire($this);
        }

        return $this;
    }

    public function removeLieuxVente(LieuxVentes $lieuxVente): static
    {
        if ($this->lieuxVentes->removeElement($lieuxVente)) {
            // set the owning side to null (unless already changed)
            if ($lieuxVente->getGestionnaire() === $this) {
                $lieuxVente->setGestionnaire(null);
            }
        }

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
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
            $listeStock->setResponsable($this);
        }

        return $this;
    }

    public function removeListeStock(ListeStock $listeStock): static
    {
        if ($this->listeStocks->removeElement($listeStock)) {
            // set the owning side to null (unless already changed)
            if ($listeStock->getResponsable() === $this) {
                $listeStock->setResponsable(null);
            }
        }

        return $this;
    }

    public function getTypeUser(): ?string
    {
        return $this->typeUser;
    }

    public function setTypeUser(?string $typeUser): static
    {
        $this->typeUser = $typeUser;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return Collection<int, Personnel>
     */
    public function getPersonnels(): Collection
    {
        return $this->personnels;
    }

    public function addPersonnel(Personnel $personnel): static
    {
        if (!$this->personnels->contains($personnel)) {
            $this->personnels->add($personnel);
            $personnel->setUser($this);
        }

        return $this;
    }

    public function removePersonnel(Personnel $personnel): static
    {
        if ($this->personnels->removeElement($personnel)) {
            // set the owning side to null (unless already changed)
            if ($personnel->getUser() === $this) {
                $personnel->setUser(null);
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
            $client->setUser($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getUser() === $this) {
                $client->setUser(null);
            }
        }

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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

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

    
    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

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
            $listeInventaire->setPersonnel($this);
        }

        return $this;
    }

    public function removeListeInventaire(ListeInventaire $listeInventaire): static
    {
        if ($this->listeInventaires->removeElement($listeInventaire)) {
            // set the owning side to null (unless already changed)
            if ($listeInventaire->getPersonnel() === $this) {
                $listeInventaire->setPersonnel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Inventaire>
     */
    public function getInventaires(): Collection
    {
        return $this->inventaires;
    }

    public function addInventaire(Inventaire $inventaire): static
    {
        if (!$this->inventaires->contains($inventaire)) {
            $this->inventaires->add($inventaire);
            $inventaire->setPersonnel($this);
        }

        return $this;
    }

    public function removeInventaire(Inventaire $inventaire): static
    {
        if ($this->inventaires->removeElement($inventaire)) {
            // set the owning side to null (unless already changed)
            if ($inventaire->getPersonnel() === $this) {
                $inventaire->setPersonnel(null);
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
            $mouvementProduct->setPersonnel($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getPersonnel() === $this) {
                $mouvementProduct->setPersonnel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MouvementProduct>
     */
    public function getMouvementProductsClient(): Collection
    {
        return $this->mouvementProductsClient;
    }

    public function addMouvementProductsClient(MouvementProduct $mouvementProductsClient): static
    {
        if (!$this->mouvementProductsClient->contains($mouvementProductsClient)) {
            $this->mouvementProductsClient->add($mouvementProductsClient);
            $mouvementProductsClient->setClient($this);
        }

        return $this;
    }

    public function removeMouvementProductsClient(MouvementProduct $mouvementProductsClient): static
    {
        if ($this->mouvementProductsClient->removeElement($mouvementProductsClient)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProductsClient->getClient() === $this) {
                $mouvementProductsClient->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AnomalieProduit>
     */
    public function getAnomalieProduits(): Collection
    {
        return $this->anomalieProduits;
    }

    public function addAnomalieProduit(AnomalieProduit $anomalieProduit): static
    {
        if (!$this->anomalieProduits->contains($anomalieProduit)) {
            $this->anomalieProduits->add($anomalieProduit);
            $anomalieProduit->setPersonnel($this);
        }

        return $this;
    }

    public function removeAnomalieProduit(AnomalieProduit $anomalieProduit): static
    {
        if ($this->anomalieProduits->removeElement($anomalieProduit)) {
            // set the owning side to null (unless already changed)
            if ($anomalieProduit->getPersonnel() === $this) {
                $anomalieProduit->setPersonnel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeTransfertProduct>
     */
    public function getListeTransfertProduct(): Collection
    {
        return $this->listeTransfertProduct;
    }

    public function addListeTransfertProduct(ListeTransfertProduct $listeTransfertProduct): static
    {
        if (!$this->listeTransfertProduct->contains($listeTransfertProduct)) {
            $this->listeTransfertProduct->add($listeTransfertProduct);
            $listeTransfertProduct->setPersonnel($this);
        }

        return $this;
    }

    public function removeListeTransfertProduct(ListeTransfertProduct $listeTransfertProduct): static
    {
        if ($this->listeTransfertProduct->removeElement($listeTransfertProduct)) {
            // set the owning side to null (unless already changed)
            if ($listeTransfertProduct->getPersonnel() === $this) {
                $listeTransfertProduct->setPersonnel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransfertProduct>
     */
    public function getTransfertProduct(): Collection
    {
        return $this->transfertProduct;
    }

    public function addTransfertProduct(TransfertProducts $transfertProduct): static
    {
        if (!$this->transfertProduct->contains($transfertProduct)) {
            $this->transfertProduct->add($transfertProduct);
            $transfertProduct->setCreePar($this);
        }

        return $this;
    }

    public function removeTransfertProduct(TransfertProducts $transfertProduct): static
    {
        if ($this->transfertProduct->removeElement($transfertProduct)) {
            // set the owning side to null (unless already changed)
            if ($transfertProduct->getCreePar() === $this) {
                $transfertProduct->setCreePar(null);
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
            $achatFournisseur->setFournisseur($this);
        }

        return $this;
    }

    public function removeAchatFournisseur(AchatFournisseur $achatFournisseur): static
    {
        if ($this->achatFournisseurs->removeElement($achatFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($achatFournisseur->getFournisseur() === $this) {
                $achatFournisseur->setFournisseur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeProductAchatFournisseur>
     */
    public function getListeProductAchatFournisseurs(): Collection
    {
        return $this->listeProductAchatFournisseurs;
    }

    public function addListeProductAchatFournisseur(ListeProductAchatFournisseur $listeProductAchatFournisseur): static
    {
        if (!$this->listeProductAchatFournisseurs->contains($listeProductAchatFournisseur)) {
            $this->listeProductAchatFournisseurs->add($listeProductAchatFournisseur);
            $listeProductAchatFournisseur->setPersonnel($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseur(ListeProductAchatFournisseur $listeProductAchatFournisseur): static
    {
        if ($this->listeProductAchatFournisseurs->removeElement($listeProductAchatFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseur->getPersonnel() === $this) {
                $listeProductAchatFournisseur->setPersonnel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AchatFournisseur>
     */
    public function getAchat(): Collection
    {
        return $this->achat;
    }

    public function addAchat(AchatFournisseur $achat): static
    {
        if (!$this->achat->contains($achat)) {
            $this->achat->add($achat);
            $achat->setPersonnel($this);
        }

        return $this;
    }

    public function removeAchat(AchatFournisseur $achat): static
    {
        if ($this->achat->removeElement($achat)) {
            // set the owning side to null (unless already changed)
            if ($achat->getPersonnel() === $this) {
                $achat->setPersonnel(null);
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
            $decaissement->setClient($this);
        }

        return $this;
    }

    public function removeDecaissement(Decaissement $decaissement): static
    {
        if ($this->decaissements->removeElement($decaissement)) {
            // set the owning side to null (unless already changed)
            if ($decaissement->getClient() === $this) {
                $decaissement->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Decaissement>
     */
    public function getDecaissementPersonnel(): Collection
    {
        return $this->decaissementPersonnel;
    }

    public function addDecaissementPersonnel(Decaissement $decaissementPersonnel): static
    {
        if (!$this->decaissementPersonnel->contains($decaissementPersonnel)) {
            $this->decaissementPersonnel->add($decaissementPersonnel);
            $decaissementPersonnel->setTraitePar($this);
        }

        return $this;
    }

    public function removeDecaissementPersonnel(Decaissement $decaissementPersonnel): static
    {
        if ($this->decaissementPersonnel->removeElement($decaissementPersonnel)) {
            // set the owning side to null (unless already changed)
            if ($decaissementPersonnel->getTraitePar() === $this) {
                $decaissementPersonnel->setTraitePar(null);
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
            $mouvementCollaborateur->setCollaborateur($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getCollaborateur() === $this) {
                $mouvementCollaborateur->setCollaborateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MouvementCollaborateur>
     */
    public function getMouvementPersonnels(): Collection
    {
        return $this->mouvementPersonnels;
    }

    public function addMouvementPersonnel(MouvementCollaborateur $mouvementPersonnel): static
    {
        if (!$this->mouvementPersonnels->contains($mouvementPersonnel)) {
            $this->mouvementPersonnels->add($mouvementPersonnel);
            $mouvementPersonnel->setTraitePar($this);
        }

        return $this;
    }

    public function removeMouvementPersonnel(MouvementCollaborateur $mouvementPersonnel): static
    {
        if ($this->mouvementPersonnels->removeElement($mouvementPersonnel)) {
            // set the owning side to null (unless already changed)
            if ($mouvementPersonnel->getTraitePar() === $this) {
                $mouvementPersonnel->setTraitePar(null);
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
            $bonCommandeFournisseur->setFournisseur($this);
        }

        return $this;
    }

    public function removeBonCommandeFournisseur(BonCommandeFournisseur $bonCommandeFournisseur): static
    {
        if ($this->bonCommandeFournisseurs->removeElement($bonCommandeFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($bonCommandeFournisseur->getFournisseur() === $this) {
                $bonCommandeFournisseur->setFournisseur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BonCommandeFournisseur>
     */
    public function getBonCommandeFournisseursPersonnel(): Collection
    {
        return $this->bonCommandeFournisseursPersonnel;
    }

    public function addBonCommandeFournisseursPersonnel(BonCommandeFournisseur $bonCommandeFournisseursPersonnel): static
    {
        if (!$this->bonCommandeFournisseursPersonnel->contains($bonCommandeFournisseursPersonnel)) {
            $this->bonCommandeFournisseursPersonnel->add($bonCommandeFournisseursPersonnel);
            $bonCommandeFournisseursPersonnel->setPersonnel($this);
        }

        return $this;
    }

    public function removeBonCommandeFournisseursPersonnel(BonCommandeFournisseur $bonCommandeFournisseursPersonnel): static
    {
        if ($this->bonCommandeFournisseursPersonnel->removeElement($bonCommandeFournisseursPersonnel)) {
            // set the owning side to null (unless already changed)
            if ($bonCommandeFournisseursPersonnel->getPersonnel() === $this) {
                $bonCommandeFournisseursPersonnel->setPersonnel(null);
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
            $absencesPersonnel->setPersonnel($this);
        }

        return $this;
    }

    public function removeAbsencesPersonnel(AbsencesPersonnels $absencesPersonnel): static
    {
        if ($this->absencesPersonnels->removeElement($absencesPersonnel)) {
            // set the owning side to null (unless already changed)
            if ($absencesPersonnel->getPersonnel() === $this) {
                $absencesPersonnel->setPersonnel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AbsencesPersonnels>
     */
    public function getAbsencesPersonnelsSaisie(): Collection
    {
        return $this->absencesPersonnelsSaisie;
    }

    public function addAbsencesPersonnelsSaisie(AbsencesPersonnels $absencesPersonnelsSaisie): static
    {
        if (!$this->absencesPersonnelsSaisie->contains($absencesPersonnelsSaisie)) {
            $this->absencesPersonnelsSaisie->add($absencesPersonnelsSaisie);
            $absencesPersonnelsSaisie->setSaisiePar($this);
        }

        return $this;
    }

    public function removeAbsencesPersonnelsSaisie(AbsencesPersonnels $absencesPersonnelsSaisie): static
    {
        if ($this->absencesPersonnelsSaisie->removeElement($absencesPersonnelsSaisie)) {
            // set the owning side to null (unless already changed)
            if ($absencesPersonnelsSaisie->getSaisiePar() === $this) {
                $absencesPersonnelsSaisie->setSaisiePar(null);
            }
        }

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
            $primesPersonnel->setPersonnel($this);
        }

        return $this;
    }

    public function removePrimesPersonnel(PrimesPersonnel $primesPersonnel): static
    {
        if ($this->primesPersonnels->removeElement($primesPersonnel)) {
            // set the owning side to null (unless already changed)
            if ($primesPersonnel->getPersonnel() === $this) {
                $primesPersonnel->setPersonnel(null);
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
            $avanceSalaire->setUser($this);
        }

        return $this;
    }

    public function removeAvanceSalaire(AvanceSalaire $avanceSalaire): static
    {
        if ($this->avanceSalaires->removeElement($avanceSalaire)) {
            // set the owning side to null (unless already changed)
            if ($avanceSalaire->getUser() === $this) {
                $avanceSalaire->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AvanceSalaire>
     */
    public function getAvanceSalaireTraite(): Collection
    {
        return $this->avanceSalaireTraite;
    }

    public function addAvanceSalaireTraite(AvanceSalaire $avanceSalaireTraite): static
    {
        if (!$this->avanceSalaireTraite->contains($avanceSalaireTraite)) {
            $this->avanceSalaireTraite->add($avanceSalaireTraite);
            $avanceSalaireTraite->setTraitePar($this);
        }

        return $this;
    }

    public function removeAvanceSalaireTraite(AvanceSalaire $avanceSalaireTraite): static
    {
        if ($this->avanceSalaireTraite->removeElement($avanceSalaireTraite)) {
            // set the owning side to null (unless already changed)
            if ($avanceSalaireTraite->getTraitePar() === $this) {
                $avanceSalaireTraite->setTraitePar(null);
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
            $paiementSalairePersonnel->setPersonnel($this);
        }

        return $this;
    }

    public function removePaiementSalairePersonnel(PaiementSalairePersonnel $paiementSalairePersonnel): static
    {
        if ($this->paiementSalairePersonnels->removeElement($paiementSalairePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($paiementSalairePersonnel->getPersonnel() === $this) {
                $paiementSalairePersonnel->setPersonnel(null);
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
            $modifDecaissement->setTraitePar($this);
        }

        return $this;
    }

    public function removeModifDecaissement(ModifDecaissement $modifDecaissement): static
    {
        if ($this->modifDecaissements->removeElement($modifDecaissement)) {
            // set the owning side to null (unless already changed)
            if ($modifDecaissement->getTraitePar() === $this) {
                $modifDecaissement->setTraitePar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ModifDecaissement>
     */
    public function getModifDecClients(): Collection
    {
        return $this->modifDecClients;
    }

    public function addModifDecClient(ModifDecaissement $modifDecClient): static
    {
        if (!$this->modifDecClients->contains($modifDecClient)) {
            $this->modifDecClients->add($modifDecClient);
            $modifDecClient->setClient($this);
        }

        return $this;
    }

    public function removeModifDecClient(ModifDecaissement $modifDecClient): static
    {
        if ($this->modifDecClients->removeElement($modifDecClient)) {
            // set the owning side to null (unless already changed)
            if ($modifDecClient->getClient() === $this) {
                $modifDecClient->setClient(null);
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
            $depense->setTraitePar($this);
        }

        return $this;
    }

    public function removeDepense(Depenses $depense): static
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getTraitePar() === $this) {
                $depense->setTraitePar(null);
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
            $versement->setClient($this);
        }

        return $this;
    }

    public function removeVersement(Versement $versement): static
    {
        if ($this->versements->removeElement($versement)) {
            // set the owning side to null (unless already changed)
            if ($versement->getClient() === $this) {
                $versement->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Versement>
     */
    public function getVersementTraite(): Collection
    {
        return $this->versementTraite;
    }

    public function addVersementTraite(Versement $versementTraite): static
    {
        if (!$this->versementTraite->contains($versementTraite)) {
            $this->versementTraite->add($versementTraite);
            $versementTraite->setTraitePar($this);
        }

        return $this;
    }

    public function removeVersementTraite(Versement $versementTraite): static
    {
        if ($this->versementTraite->removeElement($versementTraite)) {
            // set the owning side to null (unless already changed)
            if ($versementTraite->getTraitePar() === $this) {
                $versementTraite->setTraitePar(null);
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
            $modification->setCollaborateur($this);
        }

        return $this;
    }

    public function removeModification(Modification $modification): static
    {
        if ($this->modifications->removeElement($modification)) {
            // set the owning side to null (unless already changed)
            if ($modification->getCollaborateur() === $this) {
                $modification->setCollaborateur(null);
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
            $transfertFond->setTraitePar($this);
        }

        return $this;
    }

    public function removeTransfertFond(TransfertFond $transfertFond): static
    {
        if ($this->transfertFonds->removeElement($transfertFond)) {
            // set the owning side to null (unless already changed)
            if ($transfertFond->getTraitePar() === $this) {
                $transfertFond->setTraitePar(null);
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
            $chequeEspece->setCollaborateur($this);
        }

        return $this;
    }

    public function removeChequeEspece(ChequeEspece $chequeEspece): static
    {
        if ($this->chequeEspeces->removeElement($chequeEspece)) {
            // set the owning side to null (unless already changed)
            if ($chequeEspece->getCollaborateur() === $this) {
                $chequeEspece->setCollaborateur(null);
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
            $recette->setSaisiePar($this);
        }

        return $this;
    }

    public function removeRecette(Recette $recette): static
    {
        if ($this->recettes->removeElement($recette)) {
            // set the owning side to null (unless already changed)
            if ($recette->getSaisiePar() === $this) {
                $recette->setSaisiePar(null);
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
            $ajustementSolde->setCollaborateur($this);
        }

        return $this;
    }

    public function removeAjustementSolde(AjustementSolde $ajustementSolde): static
    {
        if ($this->ajustementSoldes->removeElement($ajustementSolde)) {
            // set the owning side to null (unless already changed)
            if ($ajustementSolde->getCollaborateur() === $this) {
                $ajustementSolde->setCollaborateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AjustementSolde>
     */
    public function getAjustementSoldeTraite(): Collection
    {
        return $this->ajustementSoldeTraite;
    }

    public function addAjustementSoldeTraite(AjustementSolde $ajustementSoldeTraite): static
    {
        if (!$this->ajustementSoldeTraite->contains($ajustementSoldeTraite)) {
            $this->ajustementSoldeTraite->add($ajustementSoldeTraite);
            $ajustementSoldeTraite->setTraitePar($this);
        }

        return $this;
    }

    public function removeAjustementSoldeTraite(AjustementSolde $ajustementSoldeTraite): static
    {
        if ($this->ajustementSoldeTraite->removeElement($ajustementSoldeTraite)) {
            // set the owning side to null (unless already changed)
            if ($ajustementSoldeTraite->getTraitePar() === $this) {
                $ajustementSoldeTraite->setTraitePar(null);
            }
        }

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    
    /**
     * @return Collection<int, Livraison>
     */
    public function getLivraisons(): Collection
    {
        return $this->livraisons;
    }

    public function addLivraison(Livraison $livraison): static
    {
        if (!$this->livraisons->contains($livraison)) {
            $this->livraisons->add($livraison);
            $livraison->setSaisiePar($this);
        }

        return $this;
    }

    public function removeLivraison(Livraison $livraison): static
    {
        if ($this->livraisons->removeElement($livraison)) {
            // set the owning side to null (unless already changed)
            if ($livraison->getSaisiePar() === $this) {
                $livraison->setSaisiePar(null);
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
            $facturation->setClient($this);
        }

        return $this;
    }

    public function removeFacturation(Facturation $facturation): static
    {
        if ($this->facturations->removeElement($facturation)) {
            // set the owning side to null (unless already changed)
            if ($facturation->getClient() === $this) {
                $facturation->setClient(null);
            }
        }

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
            $commissionVente->setBeneficiaire($this);
        }

        return $this;
    }

    public function removeCommissionVente(CommissionVente $commissionVente): static
    {
        if ($this->commissionVentes->removeElement($commissionVente)) {
            // set the owning side to null (unless already changed)
            if ($commissionVente->getBeneficiaire() === $this) {
                $commissionVente->setBeneficiaire(null);
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
            $modificationFacture->setSaisiePar($this);
        }

        return $this;
    }

    public function removeModificationFacture(ModificationFacture $modificationFacture): static
    {
        if ($this->modificationFactures->removeElement($modificationFacture)) {
            // set the owning side to null (unless already changed)
            if ($modificationFacture->getSaisiePar() === $this) {
                $modificationFacture->setSaisiePar(null);
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
            $proformat->setClient($this);
        }

        return $this;
    }

    public function removeProformat(Proformat $proformat): static
    {
        if ($this->proformats->removeElement($proformat)) {
            // set the owning side to null (unless already changed)
            if ($proformat->getClient() === $this) {
                $proformat->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SuppressionFacture>
     */
    public function getSuppressionFacturesClient(): Collection
    {
        return $this->suppressionFacturesClient;
    }

    public function addSuppressionFacturesClient(SuppressionFacture $suppressionFacturesClient): static
    {
        if (!$this->suppressionFacturesClient->contains($suppressionFacturesClient)) {
            $this->suppressionFacturesClient->add($suppressionFacturesClient);
            $suppressionFacturesClient->setClient($this);
        }

        return $this;
    }

    public function removeSuppressionFacturesClient(SuppressionFacture $suppressionFacturesClient): static
    {
        if ($this->suppressionFacturesClient->removeElement($suppressionFacturesClient)) {
            // set the owning side to null (unless already changed)
            if ($suppressionFacturesClient->getClient() === $this) {
                $suppressionFacturesClient->setClient(null);
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
            $suppressionFacture->setSaisiePar($this);
        }

        return $this;
    }

    public function removeSuppressionFacture(SuppressionFacture $suppressionFacture): static
    {
        if ($this->suppressionFactures->removeElement($suppressionFacture)) {
            // set the owning side to null (unless already changed)
            if ($suppressionFacture->getSaisiePar() === $this) {
                $suppressionFacture->setSaisiePar(null);
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
            $retourProduct->setSaisiePar($this);
        }

        return $this;
    }

    public function removeRetourProduct(RetourProduct $retourProduct): static
    {
        if ($this->retourProducts->removeElement($retourProduct)) {
            // set the owning side to null (unless already changed)
            if ($retourProduct->getSaisiePar() === $this) {
                $retourProduct->setSaisiePar(null);
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
            $echangeDevise->setSaisiePar($this);
        }

        return $this;
    }

    public function removeEchangeDevise(EchangeDevise $echangeDevise): static
    {
        if ($this->echangeDevises->removeElement($echangeDevise)) {
            // set the owning side to null (unless already changed)
            if ($echangeDevise->getSaisiePar() === $this) {
                $echangeDevise->setSaisiePar(null);
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
            $mouvementCaiss->setSaisiePar($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getSaisiePar() === $this) {
                $mouvementCaiss->setSaisiePar(null);
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
            $gestionCheque->setTraitePar($this);
        }

        return $this;
    }

    public function removeGestionCheque(GestionCheque $gestionCheque): static
    {
        if ($this->gestionCheques->removeElement($gestionCheque)) {
            // set the owning side to null (unless already changed)
            if ($gestionCheque->getTraitePar() === $this) {
                $gestionCheque->setTraitePar(null);
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
            $clotureCaiss->setSaisiePar($this);
        }

        return $this;
    }

    public function removeClotureCaiss(ClotureCaisse $clotureCaiss): static
    {
        if ($this->clotureCaisses->removeElement($clotureCaiss)) {
            // set the owning side to null (unless already changed)
            if ($clotureCaiss->getSaisiePar() === $this) {
                $clotureCaiss->setSaisiePar(null);
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
            $retourProductFournisseur->setSaisiePar($this);
        }

        return $this;
    }

    public function removeRetourProductFournisseur(RetourProductFournisseur $retourProductFournisseur): static
    {
        if ($this->retourProductFournisseurs->removeElement($retourProductFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($retourProductFournisseur->getSaisiePar() === $this) {
                $retourProductFournisseur->setSaisiePar(null);
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
            $factureFrai->setFournisseur($this);
        }

        return $this;
    }

    public function removeFactureFrai(FactureFrais $factureFrai): static
    {
        if ($this->factureFrais->removeElement($factureFrai)) {
            // set the owning side to null (unless already changed)
            if ($factureFrai->getFournisseur() === $this) {
                $factureFrai->setFournisseur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeProductAchatFournisseurFrais>
     */
    public function getListeProductAchatFournisseurFrais(): Collection
    {
        return $this->listeProductAchatFournisseurFrais;
    }

    public function addListeProductAchatFournisseurFrai(ListeProductAchatFournisseurFrais $listeProductAchatFournisseurFrai): static
    {
        if (!$this->listeProductAchatFournisseurFrais->contains($listeProductAchatFournisseurFrai)) {
            $this->listeProductAchatFournisseurFrais->add($listeProductAchatFournisseurFrai);
            $listeProductAchatFournisseurFrai->setPersonnel($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseurFrai(ListeProductAchatFournisseurFrais $listeProductAchatFournisseurFrai): static
    {
        if ($this->listeProductAchatFournisseurFrais->removeElement($listeProductAchatFournisseurFrai)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseurFrai->getPersonnel() === $this) {
                $listeProductAchatFournisseurFrai->setPersonnel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeProductAchatFournisseurMultiple>
     */
    public function getListeProductAchatFournisseurMultiples(): Collection
    {
        return $this->listeProductAchatFournisseurMultiples;
    }

    public function addListeProductAchatFournisseurMultiple(ListeProductAchatFournisseurMultiple $listeProductAchatFournisseurMultiple): static
    {
        if (!$this->listeProductAchatFournisseurMultiples->contains($listeProductAchatFournisseurMultiple)) {
            $this->listeProductAchatFournisseurMultiples->add($listeProductAchatFournisseurMultiple);
            $listeProductAchatFournisseurMultiple->setPersonnel($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseurMultiple(ListeProductAchatFournisseurMultiple $listeProductAchatFournisseurMultiple): static
    {
        if ($this->listeProductAchatFournisseurMultiples->removeElement($listeProductAchatFournisseurMultiple)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseurMultiple->getPersonnel() === $this) {
                $listeProductAchatFournisseurMultiple->setPersonnel(null);
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
            $achatFournisseurGeneral->setFournisseur($this);
        }

        return $this;
    }

    public function removeAchatFournisseurGeneral(AchatFournisseurGeneral $achatFournisseurGeneral): static
    {
        if ($this->achatFournisseurGenerals->removeElement($achatFournisseurGeneral)) {
            // set the owning side to null (unless already changed)
            if ($achatFournisseurGeneral->getFournisseur() === $this) {
                $achatFournisseurGeneral->setFournisseur(null);
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
            $promotion->setSaisiePar($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): static
    {
        if ($this->promotions->removeElement($promotion)) {
            // set the owning side to null (unless already changed)
            if ($promotion->getSaisiePar() === $this) {
                $promotion->setSaisiePar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SortieStock>
     */
    public function getSortieStocks(): Collection
    {
        return $this->sortieStocks;
    }

    public function addSortieStock(SortieStock $sortieStock): static
    {
        if (!$this->sortieStocks->contains($sortieStock)) {
            $this->sortieStocks->add($sortieStock);
            $sortieStock->setSaisiePar($this);
        }

        return $this;
    }

    public function removeSortieStock(SortieStock $sortieStock): static
    {
        if ($this->sortieStocks->removeElement($sortieStock)) {
            // set the owning side to null (unless already changed)
            if ($sortieStock->getSaisiePar() === $this) {
                $sortieStock->setSaisiePar(null);
            }
        }

        return $this;
    }

    
}
