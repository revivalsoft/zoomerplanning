<?php

namespace App\Entity;

use App\Repository\HierarchicRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HierarchicRepository::class)]
class Hierarchic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $groupe_id = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $position = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupeId(): ?int
    {
        return $this->groupe_id;
    }

    public function setGroupeId(int $groupe_id): static
    {
        $this->groupe_id = $groupe_id;

        return $this;
    }

    public function getPosition(): ?array
    {
        return $this->position;
    }

    public function setPosition(?array $position): static
    {
        $this->position = $position;

        return $this;
    }
}
