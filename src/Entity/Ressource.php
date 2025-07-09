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

use App\Repository\RessourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\PushSubscription;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RessourceRepository::class)]
class Ressource implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15)]
    private ?string $nom = null;

    #[ORM\Column(length: 30)]
    private ?string $fonction = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'ressource', targetEntity: PushSubscription::class)]
    private Collection $pushSubscriptions;

    #[ORM\ManyToMany(targetEntity: Groupe::class, inversedBy: 'ressources')]
    #[ORM\JoinTable(name: 'ressource_groupe')]
    private Collection $groupe;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $matricule = null;

    #[ORM\OneToMany(mappedBy: 'ressource', targetEntity: GtaskResource::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $gtaskResources;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];


    #[ORM\ManyToMany(targetEntity: Groupe::class)]
    #[ORM\JoinTable(name: 'admin_groupe')]
    private Collection $groupesAdministres;



    public function __construct()
    {
        $this->groupe = new ArrayCollection();
        $this->gtaskResources = new ArrayCollection();
        $this->pushSubscriptions = new ArrayCollection();
        $this->groupesAdministres = new ArrayCollection();
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

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): static
    {
        $this->fonction = $fonction;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = strtolower($email); // évite les doublons
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function eraseCredentials(): void
    {
        // nettoyer les données sensibles temporaires ici si nécessaire
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // rôle par défaut
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->nom ?? $this->email;
    }

    /**
     * @return Collection<int, Groupe>
     */
    public function getGroupe(): Collection
    {
        return $this->groupe;
    }

    public function addGroupe(Groupe $groupe): static
    {
        if (!$this->groupe->contains($groupe)) {
            $this->groupe->add($groupe);
            $groupe->addRessource($this); // synchronisation inverse
        }
        return $this;
    }

    public function removeGroupe(Groupe $groupe): static
    {
        if ($this->groupe->removeElement($groupe)) {
            $groupe->removeRessource($this); // synchronisation inverse
        }
        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(?string $matricule): static
    {
        $this->matricule = $matricule;
        return $this;
    }

    /**
     * @return Collection<int, GtaskResource>
     */
    public function getGtaskResources(): Collection
    {
        return $this->gtaskResources;
    }

    public function addGtaskResource(GtaskResource $gtaskResource): static
    {
        if (!$this->gtaskResources->contains($gtaskResource)) {
            $this->gtaskResources->add($gtaskResource);
            $gtaskResource->setRessource($this);
        }
        return $this;
    }

    public function removeGtaskResource(GtaskResource $gtaskResource): static
    {
        if ($this->gtaskResources->removeElement($gtaskResource)) {
            if ($gtaskResource->getRessource() === $this) {
                $gtaskResource->setRessource(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }

    public function getAdminInfo(): string
    {
        $role = '';
        if (in_array('ROLE_SUPER_ADMIN', $this->getRoles(), true)) {
            $role = 'ROLE_SUPER_ADMIN';
        } elseif (in_array('ROLE_ADMIN', $this->getRoles(), true)) {
            $role = 'ROLE_ADMIN';
        } else {
            $role = 'ROLE_USER';
        }

        return sprintf('%s (%s)', $this->getNom(), $role);
    }

    /**
     * @return Collection<int, PushSubscription>
     */
    public function getPushSubscriptions(): Collection
    {
        return $this->pushSubscriptions;
    }

    public function addPushSubscription(PushSubscription $subscription): static
    {
        if (!$this->pushSubscriptions->contains($subscription)) {
            $this->pushSubscriptions->add($subscription);
            $subscription->setRessource($this);
        }

        return $this;
    }

    public function removePushSubscription(PushSubscription $subscription): static
    {
        if ($this->pushSubscriptions->removeElement($subscription)) {
            if ($subscription->getRessource() === $this) {
                $subscription->setRessource(null);
            }
        }

        return $this;
    }

    public function getGroupesAdministres(): Collection
    {
        return $this->groupesAdministres;
    }
    public function setGroupesAdministres(Collection $groupes): static
    {
        $this->groupesAdministres = $groupes;
        return $this;
    }

    public function addGroupeAdministre(Groupe $groupe): static
    {
        if (!$this->groupesAdministres->contains($groupe)) {
            $this->groupesAdministres->add($groupe);
        }

        return $this;
    }

    public function removeGroupeAdministre(Groupe $groupe): static
    {
        $this->groupesAdministres->removeElement($groupe);

        return $this;
    }
}
