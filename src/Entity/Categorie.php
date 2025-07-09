<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\ManyToMany(targetEntity: Plage::class, mappedBy: 'categorie')]
    private Collection $plage;

    #[ORM\Column]
    private ?bool $visible = null;

    public function __construct()
    {
        $this->plage = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Plage>
     */
    public function getPlage(): Collection
    {
        return $this->plage;
    }

    public function addPlage(Plage $plage): static
    {
        if (!$this->plage->contains($plage)) {
            $this->plage->add($plage);
            $plage->addCategorie($this);
        }

        return $this;
    }

    public function removePlage(Plage $plage): static
    {
        if ($this->plage->removeElement($plage)) {
            $plage->removeCategorie($this);
        }

        return $this;
    }

    // Méthode __toString() pour afficher le nom de la catégorie
    public function __toString(): string
    {
        return $this->nom; // Assurez-vous que $name est une chaîne de caractères
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }
}
