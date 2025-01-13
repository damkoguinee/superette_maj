<?php

namespace App\Entity;

use App\Repository\ListeProductAchatFournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeProductAchatFournisseurRepository::class)]
class ListeProductAchatFournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AchatFournisseur $achatFournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column]
    private ?float $quantite = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixAchat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixRevient = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?ListeStock $stock = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\OneToMany(mappedBy: 'achatFournisseur', targetEntity: MouvementProduct::class, orphanRemoval:true, cascade : ['persist', 'remove'])]
    private Collection $mouvementProducts;

    #[ORM\Column(nullable: true)]
    private ?float $taux = null;

    #[ORM\OneToMany(mappedBy: 'listeProductAchat', targetEntity: RetourProductFournisseur::class, orphanRemoval:true, cascade : ['persist', 'remove'])]
    private Collection $retourProductFournisseurs;

    #[ORM\OneToMany(mappedBy: 'listeProductAchat', targetEntity: ListeProductAchatFournisseurMultiple::class, orphanRemoval:true, cascade : ['persist', 'remove'])]
    private Collection $listeProductAchatFournisseurMultiples;

    #[ORM\Column(nullable: true)]
    private ?float $quantiteLivre = null;

    

    public function __construct()
    {
        $this->mouvementProducts = new ArrayCollection();
        $this->retourProductFournisseurs = new ArrayCollection();
        $this->listeProductAchatFournisseurMultiples = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAchatFournisseur(): ?AchatFournisseur
    {
        return $this->achatFournisseur;
    }

    public function setAchatFournisseur(?AchatFournisseur $achatFournisseur): static
    {
        $this->achatFournisseur = $achatFournisseur;

        return $this;
    }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getPrixAchat(): ?string
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?string $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getPrixRevient(): ?string
    {
        return $this->prixRevient;
    }

    public function setPrixRevient(?string $prixRevient): static
    {
        $this->prixRevient = $prixRevient;

        return $this;
    }

    public function getStock(): ?ListeStock
    {
        return $this->stock;
    }

    public function setStock(?ListeStock $stock): static
    {
        $this->stock = $stock;

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

    public function getPersonnel(): ?User
    {
        return $this->personnel;
    }

    public function setPersonnel(?User $personnel): static
    {
        $this->personnel = $personnel;

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
            $mouvementProduct->setAchatFournisseur($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getAchatFournisseur() === $this) {
                $mouvementProduct->setAchatFournisseur(null);
            }
        }

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(?float $taux): static
    {
        $this->taux = $taux;

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
            $retourProductFournisseur->setListeProductAchat($this);
        }

        return $this;
    }

    public function removeRetourProductFournisseur(RetourProductFournisseur $retourProductFournisseur): static
    {
        if ($this->retourProductFournisseurs->removeElement($retourProductFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($retourProductFournisseur->getListeProductAchat() === $this) {
                $retourProductFournisseur->setListeProductAchat(null);
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
            $listeProductAchatFournisseurMultiple->setListeProductAchat($this);
        }

        return $this;
    }

    public function removeListeProductAchatFournisseurMultiple(ListeProductAchatFournisseurMultiple $listeProductAchatFournisseurMultiple): static
    {
        if ($this->listeProductAchatFournisseurMultiples->removeElement($listeProductAchatFournisseurMultiple)) {
            // set the owning side to null (unless already changed)
            if ($listeProductAchatFournisseurMultiple->getListeProductAchat() === $this) {
                $listeProductAchatFournisseurMultiple->setListeProductAchat(null);
            }
        }

        return $this;
    }

    public function getQuantiteLivre(): ?float
    {
        return $this->quantiteLivre;
    }

    public function setQuantiteLivre(?float $quantiteLivre): static
    {
        $this->quantiteLivre = $quantiteLivre;

        return $this;
    }
}
