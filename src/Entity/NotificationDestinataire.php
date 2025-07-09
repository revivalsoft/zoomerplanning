<?php
// src/Entity/NotificationDestinataire.php
//webpush
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
class NotificationDestinataire
{
    #[ORM\Id, ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: NotificationMessage::class)]
    #[ORM\JoinColumn(nullable: false)]
    private NotificationMessage $notification;

    #[ORM\ManyToOne(targetEntity: Ressource::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Ressource $ressource;

    #[ORM\Column(type: 'boolean')]
    private bool $vue = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateVue = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotification(): NotificationMessage
    {
        return $this->notification;
    }

    public function setNotification(NotificationMessage $notification): self
    {
        $this->notification = $notification;
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

    public function getDateVue(): ?\DateTimeInterface
    {
        return $this->dateVue;
    }

    public function setDateVue(?\DateTimeInterface $dateVue): static
    {
        $this->dateVue = $dateVue;
        return $this;
    }


    public function isVue(): bool
    {
        return $this->vue;
    }

    public function setVue(bool $vue): self
    {
        $this->vue = $vue;
        if ($vue && $this->dateVue === null) {
            $this->dateVue = new \DateTime(); // date enregistrée une seule fois
        }
        return $this;
    }
}
