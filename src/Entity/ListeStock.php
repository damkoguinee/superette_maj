<?php

namespace App\Entity;

use App\Repository\ListeStockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeStockRepository::class)]
class ListeStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listeStocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\Column(length: 150)]
    private ?string $nomStock = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(nullable: true)]
    private ?float $surface = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbrePieces = null;

    #[ORM\ManyToOne(inversedBy: 'listeStocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $responsable = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\OneToMany(mappedBy: 'stockProduit', targetEntity: Stock::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $stockProduit;

    #[ORM\OneToMany(mappedBy: 'stockProduct', targetEntity: MouvementProduct::class)]
    private Collection $mouvementProducts;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: Inventaire::class)]
    private Collection $inventaires;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: AnomalieProduit::class)]
    private Collection $anomalieProduits;

    #[ORM\OneToMany(mappedBy: 'stockDepart', targetEntity: ListeTransfertProduct::class)]
    private Collection $listeTransfertProductDepart;

    #[ORM\OneToMany(mappedBy: 'stockRecep', targetEntity: ListeTransfertProduct::class)]
    private Collection $listeTransfertProductRecep;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: ListeProductAchatFournisseur::class)]
    private Collection $listeProductAchatFournisseurs;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: Livraison::class)]
    private Collection $livraisons;

    #[ORM\OneToMany(mappedBy: 'stockRetour', targetEntity: RetourProduct::class)]
    private Collection $retourProducts;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: ListeProductAchatFournisseurMultiple::class)]
    private Collection $listeProductAchatFournisseurMultiples;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: SortieStock::class)]
    private Collection $sortieStocks;

    public function __construct()
    {
        $this->stockProduit = new ArrayCollection();
        $this->mouvementProducts = new ArrayCollection();
        $this->inventaires = new ArrayCollection();
        $this->anomalieProduits = new ArrayCollection();
        $this->listeTransfertProductDepart = new ArrayCollection();
        $this->listeTransfertProductRecep = new ArrayCollection();
        $this->listeProductAchatFournisseurs = new ArrayCollection();
        $this->livraisons = new ArrayCollection();
        $this->retourProducts = new ArrayCollection();
        $this->listeProductAchatFournisseurMultiples = new ArrayCollection();
        $this->sortieStocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNomStock(): ?string
    {
        return $this->nomStock;
    }

    public function setNomStock(string $nomStock): static
    {
        $this->nomStock = $nomStock;

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

    public function getSurface(): ?float
    {
        return $this->surface;
    }

    public function setSurface(?float $surface): static
    {
        $this->surface = $surface;

        return $this;
    }

    public function getNbrePieces(): ?int
    {
        return $this->nbrePieces;
    }

    public function setNbrePieces(?int $nbrePieces): static
    {
        $this->nbrePieces = $nbrePieces;

        return $this;
    }

    public function getResponsable(): ?User
    {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): static
    {
        $this->responsable = $responsable;

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
     * @return Collection<int, Stock>
     */
    public function getStockProduit(): Collection
    {
        return $this->stockProduit;
    }

    public function addStockProduit(Stock $stockProduit): static
    {
        if (!$this->stockProduit->contains($stockProduit)) {
            $this->stockProduit->add($stockProduit);
            $stockProduit->setStockProduit($this);
        }

        return $this;
    }

    public function removeStockProduit(Stock $stockProduit): static
    {
        if ($this->stockProduit->removeElement($stockProduit)) {
            // set the owning side to null (unless already changed)
            if ($stockProduit->getStockProduit() === $this) {
                $stockProduit->setStockProduit(null);
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
            $mouvementProduct->setStockProduct($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getStockProduct() === $this) {
                $mouvementProduct->setStockProduct(null);
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
            $inventaire->setStock($this);
        }

        return $this;
    }

    public function removeInventaire(Inventaire $inventaire): static
    {
        if ($this->inventaires->removeElement($inventaire)) {
            // set the owning side to null (unless already changed)
            if ($inventaire->getStock() === $this) {
                $inventaire->setStock(null);
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
            $anomalieProduit->setStock($this);
        }

        return $this;
    }

    public function removeAnomalieProduit(AnomalieProduit $anomalieProduit): static
    {
        if ($this->anomalieProduits->removeElement($anomalieProduit)) {
            // set the owning side to null (unless already changed)
            if ($anomalieProduit->getStock() === $this) {
                $anomalieProduit->setStock(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeTransfertProduct>
     */
    public function getTransfertProductsDepart(): Collection
    {
        return $this->listeTransfertProductDepart;
    }

    public function addTransfertProductsDepart(ListeTransfertProduct $listeTransfertProductDepart): static
    {
        if (!$this->listeTransfertProductDepart->contains($listeTransfertProductDepart)) {
            $this->listeTransfertProductDepart->add($listeTransfertProductDepart);
            $listeTransfertProductDepart->setStockDepart($this);
        }

        return $this;
    }

    public function removeTransfertProductsDepart(ListeTransfertProduct $listeTransfertProductDepart): static
    {
        if ($this->listeTransfertProductDepart->removeElement($listeTransfertProductDepart)) {
            // set the owning side to null (unless already changed)
            if ($listeTransfertProductDepart->getStockDepart() === $this) {
                $listeTransfertProductDepart->setStockDepart(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeTransfertProduct>
     */
    public function getlisteTransfertProductRecep(): Collection
    {
        return $this->listeTransfertProductRecep;
    }

    public function addlisteTransfertProductRecep(ListeTransfertProduct $listeTransfertProductRecep): static
    {
        if (!$this->listeTransfertProductRecep->contains($listeTransfertProductRecep)) {
            $this->listeTransfertProductRecep->add($listeTransfertProductRecep);
            $listeTransfertProductRecep->setStockRecep($this);
        }

        return $this;
    }

    public function removelisteTransfertProductRecep(ListeTransfertProduct $listeTransfertProductRecep): static
    {
        if ($this->listeTransfertProductRecep->removeElement($listeTransfertProductRecep)) {
            // set the owning side to null (unless already changed)
            if ($listeTransfertProductRecep->getStockRecep() === $this) {
                $listeTransfertProductRecep->setStockRecep(null);
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
            $listeProductAchatFournisseur->setStock($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseur(ListeProductAchatFournisseur $listeProductAchatFournisseur): static
    {
        if ($this->listeProductAchatFournisseurs->removeElement($listeProductAchatFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseur->getStock() === $this) {
                $listeProductAchatFournisseur->setStock(null);
            }
        }

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
            $livraison->setStock($this);
        }

        return $this;
    }

    public function removeLivraison(Livraison $livraison): static
    {
        if ($this->livraisons->removeElement($livraison)) {
            // set the owning side to null (unless already changed)
            if ($livraison->getStock() === $this) {
                $livraison->setStock(null);
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
            $retourProduct->setStockRetour($this);
        }

        return $this;
    }

    public function removeRetourProduct(RetourProduct $retourProduct): static
    {
        if ($this->retourProducts->removeElement($retourProduct)) {
            // set the owning side to null (unless already changed)
            if ($retourProduct->getStockRetour() === $this) {
                $retourProduct->setStockRetour(null);
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
            $listeProductAchatFournisseurMultiple->setStock($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseurMultiple(ListeProductAchatFournisseurMultiple $listeProductAchatFournisseurMultiple): static
    {
        if ($this->listeProductAchatFournisseurMultiples->removeElement($listeProductAchatFournisseurMultiple)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseurMultiple->getStock() === $this) {
                $listeProductAchatFournisseurMultiple->setStock(null);
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
            $sortieStock->setStock($this);
        }

        return $this;
    }

    public function removeSortieStock(SortieStock $sortieStock): static
    {
        if ($this->sortieStocks->removeElement($sortieStock)) {
            // set the owning side to null (unless already changed)
            if ($sortieStock->getStock() === $this) {
                $sortieStock->setStock(null);
            }
        }

        return $this;
    }
}
