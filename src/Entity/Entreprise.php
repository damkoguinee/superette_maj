<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomEntreprise = null;

    #[ORM\Column(length: 255)]
    private ?string $identifiant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroAgrement = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 15)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $longitude = null;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: LieuxVentes::class)]
    private Collection $lieuxVentes;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Licence::class)]
    private Collection $licences;

    public function __construct()
    {
        $this->lieuxVentes = new ArrayCollection();
        $this->licences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(string $nomEntreprise): static
    {
        $this->nomEntreprise = $nomEntreprise;

        return $this;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): static
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    public function getNumeroAgrement(): ?string
    {
        return $this->numeroAgrement;
    }

    public function setNumeroAgrement(?string $numeroAgrement): static
    {
        $this->numeroAgrement = $numeroAgrement;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection<int, LieuxVentes>
     */
    public function getLieuxVentes(): Collection
    {
        return $this->lieuxVentes;
    }

    public function addLieuxVente(LieuxVentes $lieuxVente): static
    {
        if (!$this->lieuxVentes->contains($lieuxVente)) {
            $this->lieuxVentes->add($lieuxVente);
            $lieuxVente->setEntreprise($this);
        }

        return $this;
    }

    public function removeLieuxVente(LieuxVentes $lieuxVente): static
    {
        if ($this->lieuxVentes->removeElement($lieuxVente)) {
            // set the owning side to null (unless already changed)
            if ($lieuxVente->getEntreprise() === $this) {
                $lieuxVente->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Licence>
     */
    public function getLicences(): Collection
    {
        return $this->licences;
    }

    public function addLicence(Licence $licence): static
    {
        if (!$this->licences->contains($licence)) {
            $this->licences->add($licence);
            $licence->setEntreprise($this);
        }

        return $this;
    }

    public function removeLicence(Licence $licence): static
    {
        if ($this->licences->removeElement($licence)) {
            // set the owning side to null (unless already changed)
            if ($licence->getEntreprise() === $this) {
                $licence->setEntreprise(null);
            }
        }

        return $this;
    }
}
