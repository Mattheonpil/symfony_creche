<?php

namespace App\Entity;

use App\Repository\PlanningRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningRepository::class)]
class Planning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $start_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $end_time = null;

    #[ORM\Column]
    private ?bool $meal = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $actual_arrival = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $actual_departure = null;

    #[ORM\Column]
    private ?bool $absence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $absence_justification = null;

    #[ORM\ManyToOne(inversedBy: 'plannings')]
    private ?Calendar $calendar = null;

    #[ORM\ManyToOne(inversedBy: 'plannings')]
    private ?Child $child = null;

    public function __construct()
    {
        $this->meal = false;    // Par défaut, pas de repas (0)
        $this->absence = false; // Par défaut, pas d'absence
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->start_time;
    }

    public function setStartTime(?\DateTime $start_time): static
    {
        $this->start_time = $start_time;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->end_time;
    }

    public function setEndTime(?\DateTime $end_time): static
    {
        $this->end_time = $end_time;

        return $this;
    }

    public function isMeal(): ?bool
    {
        return $this->meal;
    }

    public function setMeal(bool $meal): static
    {
        $this->meal = $meal;

        return $this;
    }

    public function getActualArrival(): ?\DateTime
    {
        return $this->actual_arrival;
    }

    public function setActualArrival(?\DateTime $actual_arrival): static
    {
        $this->actual_arrival = $actual_arrival;

        return $this;
    }

    public function getActualDeparture(): ?\DateTime
    {
        return $this->actual_departure;
    }

    public function setActualDeparture(?\DateTime $actual_departure): static
    {
        $this->actual_departure = $actual_departure;

        return $this;
    }

    public function isAbsence(): ?bool
    {
        return $this->absence;
    }

    public function setAbsence(bool $absence): static
    {
        $this->absence = $absence;

        return $this;
    }

    public function getAbsenceJustification(): ?string
    {
        return $this->absence_justification;
    }

    public function setAbsenceJustification(?string $absence_justification): static
    {
        $this->absence_justification = $absence_justification;

        return $this;
    }

    public function getCalendar(): ?Calendar
    {
        return $this->calendar;
    }

    public function setCalendar(?Calendar $calendar): static
    {
        $this->calendar = $calendar;

        return $this;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): static
    {
        $this->child = $child;

        return $this;
    }

    public function updateMealStatus(): void
    {
        if ($this->start_time && $this->end_time) {
            $startHour = (int)$this->start_time->format('H');
            $endHour = (int)$this->end_time->format('H');
            
            // Si l'enfant est présent entre 12h et 14h
            if ($startHour <= 14 && $endHour >= 12) {
                $this->meal = true;  // 1 = repas prévu
            } else {
                $this->meal = false; // 0 = pas de repas
            }
        } else {
            $this->meal = false; // Par défaut, pas de repas
        }
    }
}
