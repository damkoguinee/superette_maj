<?php

namespace App\Entity;

use App\Repository\LivraisonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
class Livraison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'livraisons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CommandeProduct $commande = null;

    #[ORM\Column]
    private ?float $quantiteLiv = null;

    #[ORM\ManyToOne(inversedBy: 'livraisons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateLivraison = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\OneToMany(mappedBy: 'livraison', targetEntity: MouvementProduct::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementProducts;

    #[ORM\ManyToOne(inversedBy: 'livraisons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeStock $stock = null;

    public function __construct()
    {
        $this->mouvementProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?CommandeProduct
    {
        return $this->commande;
    }

    public function setCommande(?CommandeProduct $commande): static
    {
        $this->commande = $commande;

        return $this;
    }

    public function getQuantiteLiv(): ?float
    {
        return $this->quantiteLiv;
    }

    public function setQuantiteLiv(float $quantiteLiv): static
    {
        $this->quantiteLiv = $quantiteLiv;

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

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(\DateTimeInterface $dateLivraison): static
    {
        $this->dateLivraison = $dateLivraison;

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
            $mouvementProduct->setLivraison($this);
        }

        return $this;
    }

    public function removeMouvementProduct(MouvementProduct $mouvementProduct): static
    {
        if ($this->mouvementProducts->removeElement($mouvementProduct)) {
            // set the owning side to null (unless already changed)
            if ($mouvementProduct->getLivraison() === $this) {
                $mouvementProduct->setLivraison(null);
            }
        }

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
}
