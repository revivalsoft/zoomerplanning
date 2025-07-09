<?php

namespace App\Entity;

use App\Repository\GestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GestionRepository::class)]
#[ORM\Table(name: "gestion")]
#[ORM\UniqueConstraint(name: "unique_cell", columns: ["ressource_id", "line", "date"])]

#[ORM\HasLifecycleCallbacks]

class Gestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]


    private ?int $id = null;


    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $line = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne]
    private ?Ressource $ressource = null;

    #[ORM\ManyToOne]
    private ?Plage $plage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function setLine(int $line): static
    {
        $this->line = $line;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

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

    public function getPlage(): ?Plage
    {
        return $this->plage;
    }

    public function setPlage(?Plage $plage): static
    {
        $this->plage = $plage;

        return $this;
    }
}
