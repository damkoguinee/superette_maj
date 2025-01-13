<?php

namespace App\Entity;

use App\Repository\AdresseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdresseRepository::class)]
class Adresse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $rue = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $complementAdresse = null;

    /**
     * @Assert\NotBlank(message="Le code postal est obligatoire")
     * @Assert\Range(
     *      min = 5,
     *      minMessage = "Le code postal doit être supérieur ou égal à 5}"
     * )
     * @Assert\Type(
     *      type="integer",
     *      message="Le code postal doit être un nombre entier"
     * )
     */
    private ?int $codePostal = null;

    #[ORM\Column(length: 200)]
    private ?string $ville = null;

    #[ORM\Column(length: 200)]
    private ?string $region = null;

    #[ORM\Column(length: 200)]
    private ?string $pays = null;

    #[ORM\ManyToOne(inversedBy: 'adresses')]
    private ?User $user = null;

    #[ORM\Column(length: 15)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $nomClient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getComplementAdresse(): ?string
    {
        return $this->complementAdresse;
    }

    public function setComplementAdresse(?string $complementAdresse): static
    {
        $this->complementAdresse = $complementAdresse;

        return $this;
    }

    public function getCodePostal(): ?int
    {
        return $this->codePostal;
    }

    public function setCodePostal(?int $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    public function __toString(): string
    {

        return ucwords($this->getNomClient()) . "[br]" .
                $this->getNumero() . "-" .
                ucwords($this->getRue()) . "[br]" .
                $this->getCodePostal() . "[br]" .
                ucwords($this->getVille()) . "[br]" .
                ucwords($this->getPays());
    }

    public function setNomClient(string $nomClient): static
    {
        $this->nomClient = $nomClient;

        return $this;
    }
}
