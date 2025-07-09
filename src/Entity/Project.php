<?php
// src/Entity/Project.php

namespace App\Entity;

use App\Entity\Gtask;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublic = false;

    #[ORM\ManyToOne]
    private ?Ressource $user = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Gtask::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $gtasks;

    public function __construct()
    {
        $this->gtasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getGtasks(): Collection
    {
        return $this->gtasks;
    }

    public function addGtask(Gtask $gtask): self
    {
        if (!$this->gtasks->contains($gtask)) {
            $this->gtasks[] = $gtask;
            $gtask->setProject($this);
        }

        return $this;
    }

    public function removeGtask(Gtask $gtask): self
    {
        if ($this->gtasks->removeElement($gtask)) {
            if ($gtask->getProject() === $this) {
                $gtask->setProject(null);
            }
        }

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
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


    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;
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
