<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:course', 'get:detailCourse'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:course', 'get:detailCourse', 'get:detailSlot', 'get:reservation'])]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:course', 'get:detailCourse'])]
    private ?string $sub_title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['get:course', 'get:detailCourse'])]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:course', 'get:detailCourse'])]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['get:course', 'get:detailCourse', 'get:detailSlot', 'get:reservation'])]
    #[Assert\Length(min:1, minMessage: "Le nombre de personne doit être au minimum de {{limit}}.")]
    private ?int $user_max = null;

    #[ORM\Column]
    #[Groups(['get:course', 'get:detailCourse', 'get:detailSlot'])]
    #[Assert\Type(type: ['float', 'integer'])]
    #[Assert\NotBlank(message: "Le prix est olbigatoire")]
    #[Assert\Positive(message: "Le prix ne peut pas être négatif")]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:course', 'get:detailCourse'])]
    private ?string $location = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Slot::class, orphanRemoval: true)]
    #[Groups('get:detailCourse')]
    private Collection $slots;

    public function __construct()
    {
        $this->slots = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSubTitle(): ?string
    {
        return $this->sub_title;
    }

    public function setSubTitle(?string $sub_title): self
    {
        $this->sub_title = $sub_title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getUserMax(): ?int
    {
        return $this->user_max;
    }

    public function setUserMax(int $user_max): self
    {
        $this->user_max = $user_max;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Slot>
     */
    public function getSlots(): Collection
    {
        return $this->slots;
    }

    public function addSlot(Slot $slot): self
    {
        if (!$this->slots->contains($slot)) {
            $this->slots->add($slot);
            $slot->setCourse($this);
        }

        return $this;
    }

    public function removeSlot(Slot $slot): self
    {
        if ($this->slots->removeElement($slot)) {
            // set the owning side to null (unless already changed)
            if ($slot->getCourse() === $this) {
                $slot->setCourse(null);
            }
        }

        return $this;
    }
}
