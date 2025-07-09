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
