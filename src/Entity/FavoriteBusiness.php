<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FavoriteBusiness
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $addedAt;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Business $business = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getAddedAt(): \DateTime
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTime $addedAt): void
    {
        $this->addedAt = $addedAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): void
    {
        $this->business = $business;
    }



    public function __construct()
    {
        $this->addedAt = new \DateTime();
    }
}
