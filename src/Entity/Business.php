<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\BusinessPhoto;
use App\Entity\Review;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Business
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255)]
    private string $name;

    #[ORM\Column(type:"string", length:255)]
    private string $address;

    #[ORM\Column(type:"string", length:50)]
    private string $phone;

    #[ORM\Column(type:"string", length:255, nullable:true)]
    private ?string $website = null;

    #[ORM\Column(type:"text", nullable:true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable:false)]
    private User $owner;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable:false)]
    private Category $category;

    #[ORM\OneToMany(targetEntity: BusinessPhoto::class, mappedBy:"business", cascade:["persist", "remove"])]
    private Collection $photos;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy:"business", cascade:["persist", "remove"])]
    private Collection $reviews;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    // --- GETTERS & SETTERS ---

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getAddress(): ?string { return $this->address; }
    public function setAddress(string $address): self { $this->address = $address; return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(string $phone): self { $this->phone = $phone; return $this; }

    public function getWebsite(): ?string { return $this->website; }
    public function setWebsite(?string $website): self { $this->website = $website; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getOwner(): User { return $this->owner; }
    public function setOwner(User $owner): self { $this->owner = $owner; return $this; }

    public function getCategory(): Category { return $this->category; }
    public function setCategory(Category $category): self { $this->category = $category; return $this; }

    // --- PHOTOS ---
    /**
     * @return Collection<int, BusinessPhoto>
     */
    public function getPhotos(): Collection { return $this->photos; }

    public function addPhoto(BusinessPhoto $photo): self {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setBusiness($this);
        }
        return $this;
    }

    public function removePhoto(BusinessPhoto $photo): self {
        if ($this->photos->removeElement($photo)) {
            if ($photo->getBusiness() === $this) {
                $photo->setBusiness(null);
            }
        }
        return $this;
    }

    // --- REVIEWS ---
    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection { return $this->reviews; }

    public function addReview(Review $review): self {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setBusiness($this);
        }
        return $this;
    }

    public function removeReview(Review $review): self {
        if ($this->reviews->removeElement($review)) {
            if ($review->getBusiness() === $this) {
                $review->setBusiness(null);
            }
        }
        return $this;
    }

    // --- CALCULER NOTE MOYENNE ---
    public function getAverageRating(): ?float
    {
        $count = $this->reviews->count();
        if ($count === 0) return null;

        $total = 0;
        foreach ($this->reviews as $review) {
            $total += $review->getRating();
        }

        return (float) ($total / $count);
    }


}
