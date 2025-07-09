<?php

namespace App\Entity;

use App\Repository\ParamRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParamRepository::class)]
class Param
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $calendar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $public = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $admin = null;

    #[ORM\Column]
    private ?bool $dates = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCalendar(): ?int
    {
        return $this->calendar;
    }

    public function setCalendar(?int $calendar): static
    {
        $this->calendar = $calendar;

        return $this;
    }

    public function getPublic(): ?int
    {
        return $this->public;
    }

    public function setPublic(int $public): static
    {
        $this->public = $public;

        return $this;
    }

    public function getAdmin(): ?int
    {
        return $this->admin;
    }

    public function setAdmin(int $admin): static
    {
        $this->admin = $admin;

        return $this;
    }

    public function isDates(): ?bool
    {
        return $this->dates;
    }

    public function setDates(bool $dates): static
    {
        $this->dates = $dates;

        return $this;
    }
}
