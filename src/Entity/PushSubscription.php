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
// src/Entity/PushSubscription.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Ressource;

#[ORM\Entity]
#[ORM\Table(name: 'push_subscriptions')]
#[ORM\UniqueConstraint(name: 'endpoint_unique', columns: ['endpoint'])]
class PushSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private string $endpoint;

    #[ORM\Column(type: 'string', length: 255)]
    private string $publicKey;

    #[ORM\Column(type: 'string', length: 255)]
    private string $authToken;

    #[ORM\Column(type: 'string', length: 20)]
    private string $contentEncoding;

    #[ORM\Column(type: "string", length: 255)]
    private string $p256dh;

    #[ORM\Column(type: "string", length: 255)]
    private string $auth;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Ressource::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ressource $ressource = null;

    public function getRessource(): ?Ressource
    {
        return $this->ressource;
    }

    public function setRessource(?Ressource $ressource): self
    {
        $this->ressource = $ressource;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getP256dh(): string
    {
        return $this->p256dh;
    }

    public function setP256dh(string $p256dh): self
    {
        $this->p256dh = $p256dh;
        return $this;
    }

    public function getAuth(): string
    {
        return $this->auth;
    }

    public function setAuth(string $auth): self
    {
        $this->auth = $auth;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $dt): self
    {
        $this->createdAt = $dt;
        return $this;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    public function getAuthToken(): ?string
    {
        return $this->authToken;
    }

    public function setAuthToken(string $authToken): self
    {
        $this->authToken = $authToken;
        return $this;
    }

    public function getContentEncoding(): ?string
    {
        return $this->contentEncoding;
    }

    public function setContentEncoding(string $contentEncoding): self
    {
        $this->contentEncoding = $contentEncoding;
        return $this;
    }
}
