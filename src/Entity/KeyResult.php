<?php
/*
 * Zoomerplanning - Logiciel de gestion des ressources humaines
 * Copyright (C) 2025 RevivalSoft
 *
 * Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou
 * le modifier selon les termes de la Licence Publique Générale GNU publiée
 * par la Free Software Foundation Version 3.
 *
 * Ce programme est distribué dans l'espoir qu'il sera utile,
 * mais SANS AUCUNE GARANTIE ; sans même la garantie implicite de
 * COMMERCIALISATION ou D’ADÉQUATION À UN BUT PARTICULIER. Voir la
 * Licence Publique Générale GNU pour plus de détails.
 *
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU
 * avec ce programme ; si ce n'est pas le cas, voir
 * <https://www.gnu.org/licenses/>.
 */
namespace App\Entity;

use App\Repository\KeyResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KeyResultRepository::class)]
class KeyResult
{



    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne]
    private ?Ressource $user = null;


    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'float')]
    private ?float $initialValue = null;

    #[ORM\Column(type: 'float')]
    private float $targetValue;

    #[ORM\Column(type: 'float')]
    private float $currentValue = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $lastUpdate;

    #[ORM\Column(type: 'boolean')]
    private bool $isAchieved = false;

    #[ORM\ManyToOne(targetEntity: Objective::class, inversedBy: 'keyResults')]
    #[ORM\JoinColumn(nullable: false)]
    private Objective $objective;

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

    public function getInitialValue(): ?float
    {
        return $this->initialValue;
    }
    // public function setInitialValue(float $value): self { $this->initialValue = $value; return $this; }
    public function setInitialValue(?float $initialValue): self
    {
        $this->initialValue = $initialValue;
        return $this;
    }

    public function getTargetValue(): float
    {
        return $this->targetValue;
    }
    public function setTargetValue(float $value): self
    {
        $this->targetValue = $value;
        return $this;
    }
    public function getCurrentValue(): float
    {
        return $this->currentValue;
    }
    public function setCurrentValue(float $value): self
    {
        $this->currentValue = $value;
        return $this;
    }
    public function getLastUpdate(): \DateTimeInterface
    {
        return $this->lastUpdate;
    }
    public function setLastUpdate(\DateTimeInterface $dt): self
    {
        $this->lastUpdate = $dt;
        return $this;
    }
    public function isAchieved(): bool
    {
        return $this->isAchieved;
    }
    public function setIsAchieved(bool $achieved): self
    {
        $this->isAchieved = $achieved;
        return $this;
    }
    public function getObjective(): Objective
    {
        return $this->objective;
    }
    public function setObjective(Objective $objective): self
    {
        $this->objective = $objective;
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

    public function getProgress(): float
    {
        $start = $this->getInitialValue(); // à ajouter dans l'entité
        $current = $this->getCurrentValue();
        $target = $this->getTargetValue();

        if ($target == $start) return 100;

        // Cas progressif (croissance attendue)
        if ($target > $start) {
            $progress = ($current - $start) / ($target - $start);
        }
        // Cas décroissant (réduction attendue)
        else {
            $progress = ($start - $current) / ($start - $target);
        }

        return round(max(0, min(1, $progress)) * 100, 2);
    }
}
