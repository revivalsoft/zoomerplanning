<?php

namespace App\Entity;

use App\Repository\ObjectiveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ObjectiveRepository::class)]

class Objective
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?Ressource $user = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $endDate;

    #[ORM\Column(type: 'boolean')]
    private bool $isClosed = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublic = false;

    #[ORM\OneToMany(mappedBy: 'objective', targetEntity: KeyResult::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $keyResults;

    public function __construct()
    {
        $this->keyResults = new ArrayCollection();
    }

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
    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }
    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }
    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }
    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }
    public function isClosed(): bool
    {
        return $this->isClosed;
    }
    public function isPublic(): bool
    {
        return $this->isPublic;
    }
    public function setIsClosed(bool $isClosed): self
    {
        $this->isClosed = $isClosed;
        return $this;
    }
    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;
        return $this;
    }
    public function getKeyResults(): Collection
    {
        return $this->keyResults;
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

    public function getProgress(): float
    {
        if (count($this->keyResults) === 0) return 0;
        $total = 0;
        foreach ($this->keyResults as $kr) {
            $total += min($kr->getCurrentValue() / $kr->getTargetValue(), 1);
        }
        return round(($total / count($this->keyResults)) * 100, 2);
    }
}
