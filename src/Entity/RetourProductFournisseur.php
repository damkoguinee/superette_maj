<?php

namespace App\Entity;

use App\Repository\RetourProductFournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RetourProductFournisseurRepository::class)]
class RetourProductFournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'retourProductFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListeProductAchatFournisseur $listeProductAchat = null;

    #[ORM\ManyToOne(inversedBy: 'retourProductFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $lieuVente = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'retourProductFournisseurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prixAchat = null;

    #[ORM\OneToMany(mappedBy: 'retourProductFournisseur', targetEntity: MouvementCollaborateur::class, orphanRemoval:true, cascade : ['remove'])]
    private Collection $mouvementCollaborateurs;

    public function __construct()
    {
        $this->mouvementCollaborateurs = new ArrayCollection();
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

    public function getLieuVente(): ?LieuxVentes
    {
        return $this->lieuVente;
    }

    public function setLieuVente(?LieuxVentes $lieuVente): static
    {
        $this->lieuVente = $lieuVente;

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

    public function getSaisiePar(): ?User
    {
        return $this->saisiePar;
    }

    public function setSaisiePar(?User $saisiePar): static
    {
        $this->saisiePar = $saisiePar;

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

    public function getPrixAchat(): ?string
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?string $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

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
            $mouvementCollaborateur->setRetourProductFournisseur($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getRetourProductFournisseur() === $this) {
                $mouvementCollaborateur->setRetourProductFournisseur(null);
            }
        }

        return $this;
    }
}
