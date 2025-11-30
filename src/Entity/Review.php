<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column]
    private int $rating;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    #[ORM\Column]
    private bool $isEdited = false;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    private ?Business $business = null;

    #[ORM\OneToMany(mappedBy: 'review', targetEntity: ReviewPhoto::class)]
    private Collection $photos;

    #[ORM\OneToMany(mappedBy: 'review', targetEntity: LikeReview::class)]
    private Collection $likes;

    #[ORM\OneToMany(mappedBy: 'review', targetEntity: ReportReview::class)]
    private Collection $reports;

    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function setPhotos(Collection $photos): void
    {
        $this->photos = $photos;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function isEdited(): bool
    {
        return $this->isEdited;
    }

    public function setIsEdited(bool $isEdited): void
    {
        $this->isEdited = $isEdited;
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

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function setLikes(Collection $likes): void
    {
        $this->likes = $likes;
    }

    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function setReports(Collection $reports): void
    {
        $this->reports = $reports;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->photos = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }
}
