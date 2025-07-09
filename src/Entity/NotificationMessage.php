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
//webpush

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\NotificationMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationMessageRepository::class)]
class NotificationMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 32, unique: true)]
    private string $accessToken;

    #[ORM\OneToMany(mappedBy: 'notification', targetEntity: NotificationDestinataire::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $destinataires;

    #[ORM\ManyToOne(targetEntity: Ressource::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ressource $auteur = null;

    public function __construct()
    {
        $this->accessToken = bin2hex(random_bytes(16));
        $this->destinataires = new ArrayCollection();
    }

    public function getAuteur(): ?Ressource
    {
        return $this->auteur;
    }

    public function setAuteur(Ressource $auteur): self
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getDestinataires(): Collection
    {
        return $this->destinataires;
    }

    public function addDestinataire(Ressource $ressource): self
    {
        $destinataire = new NotificationDestinataire();
        $destinataire->setNotification($this);
        $destinataire->setRessource($ressource);
        $destinataire->setVue(false);

        $this->destinataires->add($destinataire);

        return $this;
    }

    public function removeDestinataire(NotificationDestinataire $destinataire): self
    {
        if ($this->destinataires->contains($destinataire)) {
            $this->destinataires->removeElement($destinataire);
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }
}
