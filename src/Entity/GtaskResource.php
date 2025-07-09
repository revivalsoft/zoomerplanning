<?php

namespace App\Entity;

use App\Repository\GtaskResourceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GtaskResourceRepository::class)]
class GtaskResource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Gtask::class, inversedBy: 'gtaskResources')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gtask $gtask = null;

    #[ORM\ManyToOne(targetEntity: Ressource::class, inversedBy: 'gtaskResources')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ressource $ressource = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGtask(): ?Gtask
    {
        return $this->gtask;
    }

    public function setGtask(?Gtask $gtask): static
    {
        $this->gtask = $gtask;
        return $this;
    }

    public function getRessource(): ?Ressource
    {
        return $this->ressource;
    }

    public function setRessource(?Ressource $ressource): static
    {
        $this->ressource = $ressource;
        return $this;
    }

    public function __toString(): string
    {
        return ($this->ressource ? $this->ressource->getNom() : '') . ' sur ' . ($this->gtask ? $this->gtask->getName() : '');
    }
}
