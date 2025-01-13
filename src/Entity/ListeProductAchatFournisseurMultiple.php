<?php

namespace App\Entity;

use App\Repository\ListeProductAchatFournisseurMultipleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeProductAchatFournisseurMultipleRepository::class)]
class ListeProductAchatFournisseurMultiple
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurMultiples')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeProductAchatFournisseur $listeProductAchat = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurMultiples')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'listeProductAchatFournisseurMultiples')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeStock $stock = null;

    #[ORM\OneToMany(mappedBy: 'livraisonMultiple', targetEntity: MouvementProduct::class, orphanRemoval:true, cascade : ['persist', 'remove'])]
    private Collection $mouvementProducts;

    public function __construct()
    {
        $this->mouvementProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getListeProductAchat(): ?ListeProductAchatFournisseur
    {
        return $this->listeProductAchat;
    }

    public function setListeProductAchat(?ListeProductAchatFournisseur $listeProductAchat): static
    {
        $this->listeProductAchat = $listeProductAchat;

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

    public function getDateSaisie(): ?\DateTimeInterface
    {
        return $this->dateSaisie;
    }

    public function setDateSaisie(\DateTimeInterface $dateSaisie): static
    {
        $this->dateSaisie = $dateSaisie;

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
            $mouvementProduct->setLivraisonMultiple($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getLivraisonMultiple() === $this) {
                $mouvementProduct->setLivraisonMultiple(null);
            }
        }

        return $this;
    }
}
