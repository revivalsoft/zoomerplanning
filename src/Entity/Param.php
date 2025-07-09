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
