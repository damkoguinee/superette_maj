<?php

namespace App\Entity;

use App\Repository\FraisSupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FraisSupRepository::class)]
class FraisSup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $designation = null;

    #[ORM\OneToMany(mappedBy: 'fraisSup', targetEntity: FactureFraisSup::class)]
    private Collection $factureFraisSups;

    #[ORM\OneToMany(mappedBy: 'fraisSup', targetEntity: ProformatFraisSup::class)]
    private Collection $proformatFraisSups;

    public function __construct()
    {
        $this->factureFraisSups = new ArrayCollection();
        $this->proformatFraisSups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @return Collection<int, FactureFraisSup>
     */
    public function getFactureFraisSups(): Collection
    {
        return $this->factureFraisSups;
    }

    public function addFactureFraisSup(FactureFraisSup $factureFraisSup): static
    {
        if (!$this->factureFraisSups->contains($factureFraisSup)) {
            $this->factureFraisSups->add($factureFraisSup);
            $factureFraisSup->setFraisSup($this);
        }

        return $this;
    }

    public function removeFactureFraisSup(FactureFraisSup $factureFraisSup): static
    {
        if ($this->factureFraisSups->removeElement($factureFraisSup)) {
            // set the owning side to null (unless already changed)
            if ($factureFraisSup->getFraisSup() === $this) {
                $factureFraisSup->setFraisSup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProformatFraisSup>
     */
    public function getProformatFraisSups(): Collection
    {
        return $this->proformatFraisSups;
    }

    public function addProformatFraisSup(ProformatFraisSup $proformatFraisSup): static
    {
        if (!$this->proformatFraisSups->contains($proformatFraisSup)) {
            $this->proformatFraisSups->add($proformatFraisSup);
            $proformatFraisSup->setFraisSup($this);
        }

        return $this;
    }

    public function removeProformatFraisSup(ProformatFraisSup $proformatFraisSup): static
    {
        if ($this->proformatFraisSups->removeElement($proformatFraisSup)) {
            // set the owning side to null (unless already changed)
            if ($proformatFraisSup->getFraisSup() === $this) {
                $proformatFraisSup->setFraisSup(null);
            }
        }

        return $this;
    }
}
