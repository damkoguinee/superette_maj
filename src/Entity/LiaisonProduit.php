<?php

namespace App\Entity;

use App\Repository\LiaisonProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: LiaisonProduitRepository::class)]
#[UniqueEntity(fields: ['produit2'], message: 'Ce produit a déjà été lié avec un autre.')]
class LiaisonProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'liaisonProduit1')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $produit1 = null;

    #[ORM\ManyToOne(inversedBy: 'liaisonProduit2')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $produit2 = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProduit1(): ?Products
    {
        return $this->produit1;
    }

    public function setProduit1(?Products $produit1): static
    {
        $this->produit1 = $produit1;

        return $this;
    }

    public function getProduit2(): ?Products
    {
        return $this->produit2;
    }

    public function setProduit2(?Products $produit2): static
    {
        $this->produit2 = $produit2;

        return $this;
    }
}
