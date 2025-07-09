<?php
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