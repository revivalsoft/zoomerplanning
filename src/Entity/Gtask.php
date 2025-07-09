<?php

namespace App\Entity;

use App\Repository\GtaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GtaskRepository::class)]
class Gtask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $status = 'waiting'; // waiting, in_progress, done

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    private array $dependencyToIds = [];

    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: 'gtask_dependencies')]
    #[ORM\JoinColumn(name: 'gtask_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'depends_on_gtask_id', referencedColumnName: 'id')]
    private Collection $dependencies;

    // Ajout de la relation OneToMany vers GtaskResource
    #[ORM\OneToMany(mappedBy: 'gtask', targetEntity: GtaskResource::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $gtaskResources;



    public function __construct()
    {
        $this->dependencies = new ArrayCollection();
        $this->gtaskResources = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getDependencies(): Collection
    {
        return $this->dependencies;
    }

    public function addDependency(Gtask $gtask): self
    {
        if (!$this->dependencies->contains($gtask)) {
            $this->dependencies->add($gtask);
        }
        return $this;
    }

    public function removeDependency(Gtask $gtask): self
    {
        $this->dependencies->removeElement($gtask);
        return $this;
    }

        public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getDependencyToIds(): array
    {
        return $this->dependencyToIds;
    }

    public function setDependencyToIds(array $ids): self
    {
        $this->dependencyToIds = $ids;
        return $this;
    }

    public function getAllDownstreamGtaskIds(): array
    {
        $visited = [];
        $stack = [$this];

        while (!empty($stack)) {
            /** @var Gtask $current */
            $current = array_pop($stack);
            foreach ($current->getDependencies() as $dep) {
                if (!in_array($dep->getId(), $visited)) {
                    $visited[] = $dep->getId();
                    $stack[] = $dep;
                }
            }
        }

        return $visited;
    }

    private array $circularToIds = [];

    public function getCircularToIds(): array
    {
        return $this->circularToIds;
    }

    public function setCircularToIds(array $ids): self
    {
        $this->circularToIds = $ids;
        return $this;
    }

    public function getAllAncestorsIds(): array
    {
        $visited = [];
        $stack = $this->getDependencies()->toArray();

        while (!empty($stack)) {
            /** @var Gtask $current */
            $current = array_pop($stack);
            if (!in_array($current->getId(), $visited)) {
                $visited[] = $current->getId();
                foreach ($current->getDependencies() as $dep) {
                    $stack[] = $dep;
                }
            }
        }

        return $visited;
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
            $gtaskResource->setGtask($this);
        }
        return $this;
    }

    public function removeGtaskResource(GtaskResource $gtaskResource): static
    {
        if ($this->gtaskResources->removeElement($gtaskResource)) {
            if ($gtaskResource->getGtask() === $this) {
                $gtaskResource->setGtask(null);
            }
        }
        return $this;
    }

  
    
}