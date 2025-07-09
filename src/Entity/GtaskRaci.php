<?php
// src/Entity/GtaskRaci.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\GtaskRaciRepository;

#[ORM\Entity(repositoryClass: GtaskRaciRepository::class)]
#[ORM\Table(name: "gtask_raci")]
#[ORM\UniqueConstraint(name: "unique_assignment", columns: ["gtask_id", "ressource_id"])]
class GtaskRaci
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Gtask::class)]
    #[ORM\JoinColumn(name: "gtask_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Gtask $gtask;

    #[ORM\ManyToOne(targetEntity: Ressource::class)]
    #[ORM\JoinColumn(name: "ressource_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Ressource $ressource;

    #[ORM\Column(type: "string", length: 1)]
    private string $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGtask(): Gtask
    {
        return $this->gtask;
    }

    public function setGtask(Gtask $gtask): self
    {
        $this->gtask = $gtask;
        return $this;
    }

    public function getRessource(): Ressource
    {
        return $this->ressource;
    }

    public function setRessource(Ressource $ressource): self
    {
        $this->ressource = $ressource;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }
}
