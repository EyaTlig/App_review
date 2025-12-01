<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: "user")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: "string")]
    private string $password;

    #[ORM\Column(type: "string", length: 50)]
    private string $role;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(nullable: true)]
    private ?string $cin = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isValidated = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;



    // --- UserInterface ---
    public function getUserIdentifier(): string { return $this->email; }
    public function getRoles(): array { return ['ROLE_' . strtoupper($this->role)]; }
    public function getPassword(): string { return $this->password; }
    public function eraseCredentials(): void {}

    // --- Getters / Setters ---
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function getRole(): string { return $this->role; }
    public function setRole(string $role): self { $this->role = $role; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): self { $this->photo = $photo; return $this; }
    public function getCin(): ?string { return $this->cin; }
    public function setCin(?string $cin): self { $this->cin = $cin; return $this; }
    public function isValidated(): bool { return $this->isValidated; }
    public function setIsValidated(bool $isValidated): self { $this->isValidated = $isValidated; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): self { $this->isActive = $isActive; return $this; }

    public function getReadableRole(): string { return ucfirst(strtolower($this->role)); }
}
