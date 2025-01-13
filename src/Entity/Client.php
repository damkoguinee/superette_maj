<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'clients')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $typeClient = null;

    #[ORM\ManyToOne(inversedBy: 'clients')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LieuxVentes $rattachement = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $limitCredit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $document = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $societe = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTypeClient(): ?string
    {
        return $this->typeClient;
    }

    public function setTypeClient(string $typeClient): static
    {
        $this->typeClient = $typeClient;

        return $this;
    }

    public function getRattachement(): ?LieuxVentes
    {
        return $this->rattachement;
    }

    public function setRattachement(?LieuxVentes $rattachement): static
    {
        $this->rattachement = $rattachement;

        return $this;
    }

    public function getLimitCredit(): ?string
    {
        return $this->limitCredit;
    }

    public function setLimitCredit(?string $limitCredit): static
    {
        $this->limitCredit = $limitCredit;

        return $this;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getSociete(): ?string
    {
        return $this->societe;
    }

    public function setSociete(?string $societe): static
    {
        $this->societe = $societe;

        return $this;
    }
}
