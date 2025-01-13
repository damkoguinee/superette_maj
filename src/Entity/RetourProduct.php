<?php

namespace App\Entity;

use App\Repository\RetourProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RetourProductRepository::class)]
class RetourProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'retourProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixVente = null;

    #[ORM\ManyToOne(inversedBy: 'retourProducts')]
    private ?Caisse $caisse = null;

    #[ORM\ManyToOne(inversedBy: 'retourProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateRetour = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'retourProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeStock $stockRetour = null;

    #[ORM\ManyToOne(inversedBy: 'retourProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\ManyToOne(inversedBy: 'retourProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CommandeProduct $commande = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $remise = null;

    #[ORM\OneToMany(mappedBy: 'retourProduct', targetEntity: MouvementCollaborateur::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementCollaborateurs;

    #[ORM\OneToMany(mappedBy: 'retourProduct', targetEntity: MouvementCaisse::class, orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $mouvementCaisses;

    public function __construct()
    {
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;

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

    public function getDateRetour(): ?\DateTimeInterface
    {
        return $this->dateRetour;
    }

    public function setDateRetour(\DateTimeInterface $dateRetour): static
    {
        $this->dateRetour = $dateRetour;

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

    public function getStockRetour(): ?ListeStock
    {
        return $this->stockRetour;
    }

    public function setStockRetour(?ListeStock $stockRetour): static
    {
        $this->stockRetour = $stockRetour;

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

    public function getCommande(): ?CommandeProduct
    {
        return $this->commande;
    }

    public function setCommande(?CommandeProduct $commande): static
    {
        $this->commande = $commande;

        return $this;
    }

    public function getRemise(): ?string
    {
        return $this->remise;
    }

    public function setRemise(?string $remise): static
    {
        $this->remise = $remise;

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
            $mouvementCollaborateur->setRetourProduct($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getRetourProduct() === $this) {
                $mouvementCollaborateur->setRetourProduct(null);
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
            $mouvementCaiss->setRetourProduct($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getRetourProduct() === $this) {
                $mouvementCaiss->setRetourProduct(null);
            }
        }

        return $this;
    }
}
