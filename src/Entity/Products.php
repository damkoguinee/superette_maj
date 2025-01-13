<?php

namespace App\Entity;

use App\Entity\Stock;
use App\Entity\LiaisonProduit;
use Doctrine\DBAL\Types\Types;
use App\Entity\MouvementProduct;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: ProductsRepository::class)]
#[UniqueEntity(fields: ['reference'], message: 'La référence doit être unique.')]
#[UniqueEntity(fields: ['designation'], message: 'La désignation doit être unique.')]
#[UniqueEntity(fields: ['codeBarre'], message: 'Le code barre doit être unique.', ignoreNull: true)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $reference = null;

    #[ORM\Column(length: 150)]
    private ?string $designation = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Epaisseurs $epaisseur = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Dimensions $dimension = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?OrigineProduit $origine = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true)]
    private ?TypeProduit $type = null;

    #[ORM\OneToMany(mappedBy: 'products', targetEntity: Stock::class)]
    private Collection $stocks;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $prixVente = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: MouvementProduct::class)]
    private Collection $mouvementProducts;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeBarre = null;

    #[ORM\Column(nullable: true)]
    private ?float $nbrePiece = null;

    #[ORM\Column(nullable: true)]
    private ?float $nbrePaquet = null;

    #[ORM\Column(length: 15)]
    private ?string $typeProduit = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbreVente = null;

    #[ORM\Column(length: 10)]
    private ?string $statut = null;

    #[ORM\Column(length: 10)]
    private ?string $statutComptable = null;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    #[ORM\OneToMany(mappedBy: 'produit1', targetEntity: LiaisonProduit::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $liaisonProduit1;

    #[ORM\OneToMany(mappedBy: 'produit2', targetEntity: LiaisonProduit::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $liaisonProduit2;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Inventaire::class)]
    private Collection $inventaires;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: AnomalieProduit::class)]
    private Collection $anomalieProduits;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ListeTransfertProduct::class)]
    private Collection $listeTransfertProduct;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ListeProductAchatFournisseur::class)]
    private Collection $listeProductAchatFournisseurs;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ListeProductBonFournisseur::class)]
    private Collection $listeProductBonFournisseurs;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: CommandeProduct::class)]
    private Collection $commandeProducts;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProformatProduct::class)]
    private Collection $proformatProducts;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: RetourProduct::class)]
    private Collection $retourProducts;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ListeProductAchatFournisseurFrais::class)]
    private Collection $listeProductAchatFournisseurFrais;

    #[ORM\ManyToMany(targetEntity: Promotion::class, mappedBy: 'produits')]
    private Collection $promotions;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: SortieStock::class)]
    private Collection $sortieStocks;

    public function __construct()
    {
        // $this->ordersDetails = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->mouvementProducts = new ArrayCollection();
        $this->liaisonProduit1 = new ArrayCollection();
        $this->liaisonProduit2 = new ArrayCollection();
        $this->inventaires = new ArrayCollection();
        $this->anomalieProduits = new ArrayCollection();
        $this->listeTransfertProduct = new ArrayCollection();
        $this->listeProductAchatFournisseurs = new ArrayCollection();
        $this->listeProductBonFournisseurs = new ArrayCollection();
        $this->commandeProducts = new ArrayCollection();
        $this->proformatProducts = new ArrayCollection();
        $this->retourProducts = new ArrayCollection();
        $this->listeProductAchatFournisseurFrais = new ArrayCollection();
        $this->promotions = new ArrayCollection();
        $this->sortieStocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return trim(strtoupper($this->reference));
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return trim(ucwords($this->designation));
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getEpaisseur(): ?Epaisseurs
    {
        return $this->epaisseur;
    }

    public function setEpaisseur(?Epaisseurs $epaisseur): static
    {
        $this->epaisseur = $epaisseur;

        return $this;
    }

    public function getDimension(): ?Dimensions
    {
        return $this->dimension;
    }

    public function setDimension(?Dimensions $dimension): static
    {
        $this->dimension = $dimension;

        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): static
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setProducts($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): static
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getProducts() === $this) {
                $stock->setProducts(null);
            }
        }

        return $this;
    }

    public function getOrigine(): ?OrigineProduit
    {
        return $this->origine;
    }

    public function setOrigine(?OrigineProduit $origine): static
    {
        $this->origine = $origine;

        return $this;
    }

    public function getType(): ?TypeProduit
    {
        return $this->type;
    }

    public function setType(?TypeProduit $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPrixVente(): ?string
    {
        return $this->prixVente;
    }

    public function setPrixVente(?string $prixVente): static
    {
        $this->prixVente = $prixVente;

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
            $mouvementProduct->setProduct($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getProduct() === $this) {
                $mouvementProduct->setProduct(null);
            }
        }

        return $this;
    }

    public function getCodeBarre(): ?string
    {
        return $this->codeBarre;
    }

    public function setCodeBarre(?string $codeBarre): static
    {
        $this->codeBarre = $codeBarre;

        return $this;
    }

    public function getNbrePiece(): ?float
    {
        return $this->nbrePiece;
    }

    public function setNbrePiece(?float $nbrePiece): static
    {
        $this->nbrePiece = $nbrePiece;

        return $this;
    }

    public function getNbrePaquet(): ?float
    {
        return $this->nbrePaquet;
    }

    public function setNbrePaquet(?float $nbrePaquet): static
    {
        $this->nbrePaquet = $nbrePaquet;

        return $this;
    }

    public function getTypeProduit(): ?string
    {
        return $this->typeProduit;
    }

    public function setTypeProduit(?string $typeProduit): static
    {
        $this->typeProduit = $typeProduit;

        return $this;
    }

    public function getNbreVente(): ?int
    {
        return $this->nbreVente;
    }

    public function setNbreVente(?int $nbreVente): static
    {
        $this->nbreVente = $nbreVente;

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

    public function getStatutComptable(): ?string
    {
        return $this->statutComptable;
    }

    public function setStatutComptable(string $statutComptable): static
    {
        $this->statutComptable = $statutComptable;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(?float $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * @return Collection<int, LiaisonProduit>
     */
    public function getLiaisonProduit1(): Collection
    {
        return $this->liaisonProduit1;
    }

    public function addLiaisonProduit1(LiaisonProduit $liaisonProduit1): static
    {
        if (!$this->liaisonProduit1->contains($liaisonProduit1)) {
            $this->liaisonProduit1->add($liaisonProduit1);
            $liaisonProduit1->setProduit1($this);
        }

        return $this;
    }

    public function removeLiaisonProduit1(LiaisonProduit $liaisonProduit1): static
    {
        if ($this->liaisonProduit1->removeElement($liaisonProduit1)) {
            // set the owning side to null (unless already changed)
            if ($liaisonProduit1->getProduit1() === $this) {
                $liaisonProduit1->setProduit1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LiaisonProduit>
     */
    public function getLiaisonProduit2(): Collection
    {
        return $this->liaisonProduit2;
    }

    public function addLiaisonProduit2(LiaisonProduit $liaisonProduit2): static
    {
        if (!$this->liaisonProduit2->contains($liaisonProduit2)) {
            $this->liaisonProduit2->add($liaisonProduit2);
            $liaisonProduit2->setProduit2($this);
        }

        return $this;
    }

    public function removeLiaisonProduit2(LiaisonProduit $liaisonProduit2): static
    {
        if ($this->liaisonProduit2->removeElement($liaisonProduit2)) {
            // set the owning side to null (unless already changed)
            if ($liaisonProduit2->getProduit2() === $this) {
                $liaisonProduit2->setProduit2(null);
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
            $inventaire->setProduct($this);
        }

        return $this;
    }

    public function removeInventaire(Inventaire $inventaire): static
    {
        if ($this->inventaires->removeElement($inventaire)) {
            // set the owning side to null (unless already changed)
            if ($inventaire->getProduct() === $this) {
                $inventaire->setProduct(null);
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
            $anomalieProduit->setProduct($this);
        }

        return $this;
    }

    public function removeAnomalieProduit(AnomalieProduit $anomalieProduit): static
    {
        if ($this->anomalieProduits->removeElement($anomalieProduit)) {
            // set the owning side to null (unless already changed)
            if ($anomalieProduit->getProduct() === $this) {
                $anomalieProduit->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeTransfertProduct>
     */
    public function getTransfertProducts(): Collection
    {
        return $this->listeTransfertProduct;
    }

    public function addTransfertProduct(ListeTransfertProduct $transfertProduct): static
    {
        if (!$this->listeTransfertProduct->contains($transfertProduct)) {
            $this->listeTransfertProduct->add($transfertProduct);
            $transfertProduct->setProduct($this);
        }

        return $this;
    }

    public function removeTransfertProduct(ListeTransfertProduct $transfertProduct): static
    {
        if ($this->listeTransfertProduct->removeElement($transfertProduct)) {
            // set the owning side to null (unless already changed)
            if ($transfertProduct->getProduct() === $this) {
                $transfertProduct->setProduct(null);
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
            $listeProductAchatFournisseur->setProduct($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseur(ListeProductAchatFournisseur $listeProductAchatFournisseur): static
    {
        if ($this->listeProductAchatFournisseurs->removeElement($listeProductAchatFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseur->getProduct() === $this) {
                $listeProductAchatFournisseur->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeProductBonFournisseur>
     */
    public function getListeProductBonFournisseurs(): Collection
    {
        return $this->listeProductBonFournisseurs;
    }

    public function addListeProductBonFournisseur(ListeProductBonFournisseur $listeProductBonFournisseur): static
    {
        if (!$this->listeProductBonFournisseurs->contains($listeProductBonFournisseur)) {
            $this->listeProductBonFournisseurs->add($listeProductBonFournisseur);
            $listeProductBonFournisseur->setProduct($this);
        }

        return $this;
    }

    public function removeListeProductBonFournisseur(ListeProductBonFournisseur $listeProductBonFournisseur): static
    {
        if ($this->listeProductBonFournisseurs->removeElement($listeProductBonFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($listeProductBonFournisseur->getProduct() === $this) {
                $listeProductBonFournisseur->setProduct(null);
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
            $commandeProduct->setProduct($this);
        }

        return $this;
    }

    public function removeCommandeProduct(CommandeProduct $commandeProduct): static
    {
        if ($this->commandeProducts->removeElement($commandeProduct)) {
            // set the owning side to null (unless already changed)
            if ($commandeProduct->getProduct() === $this) {
                $commandeProduct->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProformatProduct>
     */
    public function getProformatProducts(): Collection
    {
        return $this->proformatProducts;
    }

    public function addProformatProduct(ProformatProduct $proformatProduct): static
    {
        if (!$this->proformatProducts->contains($proformatProduct)) {
            $this->proformatProducts->add($proformatProduct);
            $proformatProduct->setProduct($this);
        }

        return $this;
    }

    public function removeProformatProduct(ProformatProduct $proformatProduct): static
    {
        if ($this->proformatProducts->removeElement($proformatProduct)) {
            // set the owning side to null (unless already changed)
            if ($proformatProduct->getProduct() === $this) {
                $proformatProduct->setProduct(null);
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
            $retourProduct->setProduct($this);
        }

        return $this;
    }

    public function removeRetourProduct(RetourProduct $retourProduct): static
    {
        if ($this->retourProducts->removeElement($retourProduct)) {
            // set the owning side to null (unless already changed)
            if ($retourProduct->getProduct() === $this) {
                $retourProduct->setProduct(null);
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
            $listeProductAchatFournisseurFrai->setProduct($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseurFrai(ListeProductAchatFournisseurFrais $listeProductAchatFournisseurFrai): static
    {
        if ($this->listeProductAchatFournisseurFrais->removeElement($listeProductAchatFournisseurFrai)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseurFrai->getProduct() === $this) {
                $listeProductAchatFournisseurFrai->setProduct(null);
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
            $promotion->addProduit($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): static
    {
        if ($this->promotions->removeElement($promotion)) {
            $promotion->removeProduit($this);
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
            $sortieStock->setProduct($this);
        }

        return $this;
    }

    public function removeSortieStock(SortieStock $sortieStock): static
    {
        if ($this->sortieStocks->removeElement($sortieStock)) {
            // set the owning side to null (unless already changed)
            if ($sortieStock->getProduct() === $this) {
                $sortieStock->setProduct(null);
            }
        }

        return $this;
    }

    
}
