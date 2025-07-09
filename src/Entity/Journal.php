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

use App\Repository\JournalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JournalRepository::class)]
class Journal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $actionType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $actionDate = null;

    #[ORM\Column]
    private ?int $idRes = null;

    #[ORM\Column]
    private ?int $idSigle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ligne = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateSigle = null;


    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $mail = false;

    #[ORM\ManyToOne(targetEntity: Ressource::class)]
    #[ORM\JoinColumn(name: 'administrateur_id', referencedColumnName: 'id', nullable: true)]
    private ?Ressource $administrateur = null;

    public function getAdministrateur(): ?Ressource
    {
        return $this->administrateur;
    }

    public function setAdministrateur(?Ressource $administrateur): self
    {
        $this->administrateur = $administrateur;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionType(): ?int
    {
        return $this->actionType;
    }

    public function setActionType(int $actionType): static
    {
        $this->actionType = $actionType;

        return $this;
    }

    public function getActionDate(): ?\DateTimeInterface
    {
        return $this->actionDate;
    }

    public function setActionDate(\DateTimeInterface $actionDate): static
    {
        $this->actionDate = $actionDate;

        return $this;
    }

    public function getIdRes(): ?int
    {
        return $this->idRes;
    }

    public function setIdRes(int $idRes): static
    {
        $this->idRes = $idRes;

        return $this;
    }

    public function getIdSigle(): ?int
    {
        return $this->idSigle;
    }

    public function setIdSigle(int $idSigle): static
    {
        $this->idSigle = $idSigle;

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

    public function getLigne(): ?int
    {
        return $this->ligne;
    }

    public function setLigne(int $ligne): static
    {
        $this->ligne = $ligne;

        return $this;
    }

    public function getDateSigle(): ?\DateTimeInterface
    {
        return $this->dateSigle;
    }

    public function setDateSigle(\DateTimeInterface $dateSigle): static
    {
        $this->dateSigle = $dateSigle;

        return $this;
    }


    public function isMail(): ?bool
    {
        return $this->mail ?? false;
    }

    public function setMail(bool $mail): static
    {
        $this->mail = $mail;

        return $this;
    }
}
