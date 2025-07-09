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

use App\Repository\PlageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlageRepository::class)]
class Plage
{


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 4)]
    private ?string $sigle = null;

    #[ORM\Column(length: 30)]
    private ?string $legende = null;

    #[ORM\Column]
    private ?bool $absence = null;

    #[ORM\Column(nullable: true)]
    private ?int $heure = null;

    #[ORM\Column(nullable: true)]
    private ?int $minute = null;

    #[ORM\Column(length: 7)]
    private ?string $couleurtexte = null;

    #[ORM\Column(length: 7)]
    private ?string $couleurfond = "#ffffff"; // valeur par défaut

    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'plage')]
    private Collection $categorie;

    public function __construct()
    {
        $this->categorie = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(string $sigle): static
    {
        $this->sigle = $sigle;

        return $this;
    }

    public function getLegende(): ?string
    {
        return $this->legende;
    }

    public function setLegende(string $legende): static
    {
        $this->legende = $legende;

        return $this;
    }

    public function isAbsence(): ?bool
    {
        return $this->absence;
    }

    public function setAbsence(bool $absence): static
    {
        $this->absence = $absence;

        return $this;
    }

    public function getHeure(): ?int
    {
        return $this->heure;
    }

    public function setHeure(?int $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getMinute(): ?int
    {
        return $this->minute;
    }

    public function setMinute(?int $minute): static
    {
        $this->minute = $minute;

        return $this;
    }

    public function getCouleurtexte(): ?string
    {
        return $this->couleurtexte;
    }

    public function setCouleurtexte(string $couleurtexte): static
    {
        $this->couleurtexte = $couleurtexte;

        return $this;
    }

    public function getCouleurfond(): ?string
    {

        return $this->couleurfond;
    }

    public function setCouleurfond(string $couleurfond): static
    {

        $this->couleurfond = $couleurfond;


        return $this;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategorie(): Collection
    {
        return $this->categorie;
    }

    public function addCategorie(Categorie $categorie): static
    {
        if (!$this->categorie->contains($categorie)) {
            $this->categorie->add($categorie);
        }

        return $this;
    }

    public function removeCategorie(Categorie $categorie): static
    {
        $this->categorie->removeElement($categorie);

        return $this;
    }

    // Méthode __toString()
    public function __toString(): string
    {
        return $this->sigle;  // Ou un autre champ descriptif
    }
}
