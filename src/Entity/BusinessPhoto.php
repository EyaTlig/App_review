<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class BusinessPhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Business::class, inversedBy:"photos")]
    #[ORM\JoinColumn(nullable:false)]
    private $business;

    #[ORM\Column(type:"string", length:255)]
    private $filename;

    public function getId(): ?int { return $this->id; }

    public function getBusiness(): ?Business { return $this->business; }
    public function setBusiness(Business $business): self { $this->business = $business; return $this; }

    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(string $filename): self { $this->filename = $filename; return $this; }
}
