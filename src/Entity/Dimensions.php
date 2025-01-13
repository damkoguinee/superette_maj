<?php

namespace App\Entity;

use App\Repository\DimensionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DimensionsRepository::class)]
class Dimensions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $valeurDimension = null;
    
    #[ORM\OneToMany(mappedBy: 'dimension', targetEntity: Products::class)]
    private Collection $products;

    #[ORM\ManyToOne(inversedBy: 'dimensions')]
    private ?Categorie $categorie = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $unite = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeurDimension(): ?string
    {
        return $this->valeurDimension;
    }

    public function setValeurDimension(string $valeurDimension): static
    {
        $this->valeurDimension = $valeurDimension;

        return $this;
    }

    /**
     * @return Collection<int, Products>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Products $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setDimension($this);
        }

        return $this;
    }

    public function removeProduct(Products $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getDimension() === $this) {
                $product->setDimension(null);
            }
        }

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

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(?string $unite): static
    {
        $this->unite = $unite;

        return $this;
    }
}
