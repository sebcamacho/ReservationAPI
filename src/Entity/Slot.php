<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SlotRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SlotRepository::class)]
class Slot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:slots', 'get:detailSlot', 'get:reservation'])]
    
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['get:detailCourse', 'get:slots', 'get:detailSlot', 'get:reservation', 'get:userReservation'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column]
    #[Groups(['get:detailCourse', 'get:slots', 'get:detailSlot', 'get:reservation'])]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\ManyToOne(inversedBy: 'get:slots')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:detailSlot', 'get:reservation'])]
    private ?Course $course = null;

    #[ORM\OneToMany(mappedBy: 'slot', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\Column]
    private ?int $countReservation = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->countReservation = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTimeImmutable $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setSlot($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getSlot() === $this) {
                $reservation->setSlot(null);
            }
        }

        return $this;
    }

    public function getCountReservation(): ?int
    {
        return $this->countReservation;
    }

    public function setCountReservation(int $countReservation): self
    {
        $this->countReservation += $countReservation;

        return $this;
    }
}
