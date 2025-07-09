<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TaskRepository;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;


    #[ORM\Column(name: 'task_column', length: 20)]
    private string $column; // todo, in_progress, done

    #[ORM\Column]
    private int $position = 0;

    #[ORM\ManyToOne]
    private ?Ressource $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getColumn(): string
    {
        return $this->column;
    }
    public function setColumn(string $column): self
    {
        $this->column = $column;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getUser(): ?Ressource
    {
        return $this->user;
    }
    public function setUser(?Ressource $user): self
    {
        $this->user = $user;
        return $this;
    }
}
