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

use App\Repository\RessourceTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RessourceTokenRepository::class)]
class RessourceToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $token;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ressourceId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $adminId = null;

    public function getId(): ?int { return $this->id; }

    public function getToken(): string { return $this->token; }
    public function setToken(string $token): self { $this->token = $token; return $this; }

    public function getRessourceId(): ?int { return $this->ressourceId; }
    public function setRessourceId(?int $id): self { $this->ressourceId = $id; return $this; }

    public function getAdminId(): ?int { return $this->adminId; }
    public function setAdminId(?int $id): self { $this->adminId = $id; return $this; }
}