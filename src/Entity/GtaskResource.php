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
