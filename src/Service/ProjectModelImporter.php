<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Gtask;
use App\Entity\Dependency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectModelImporter extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Importe un modèle JSON vers un nouveau projet.
     *
     * @param string $jsonPath chemin absolu du fichier JSON
     * @param string $newProjectName nom du nouveau projet
     * @param \DateTimeInterface|null $forcedStartDate pour recalage des dates
     * @return Project
     */
    public function import(string $jsonPath, string $newProjectName, ?\DateTimeInterface $forcedStartDate = null): Project
    {
        if (!file_exists($jsonPath)) {
            throw new \RuntimeException("Fichier modèle introuvable.");
        }

        $content = file_get_contents($jsonPath);
        $data = json_decode($content, true);

        if (!$data || !isset($data['project'], $data['tasks'], $data['dependencies'])) {
            throw new \RuntimeException("Modèle JSON invalide.");
        }

        // Création du nouveau projet
        $project = new Project();
        $project->setName($newProjectName);
        $project->setDescription($data['project']['description'] ?? '');
        $project->setIsPublic($data['project']['is_public'] ?? false);
        $project->setUser($this->getUser());

        // Trouver la date de début la plus ancienne parmi toutes les tasks
        $minTaskStartDate = null;
        foreach ($data['tasks'] as $taskData) {
            $taskStart = new \DateTime($taskData['start_date']);
            if ($minTaskStartDate === null || $taskStart < $minTaskStartDate) {
                $minTaskStartDate = $taskStart;
            }
        }

        // Date de début souhaitée pour le projet (forcée ou celle du JSON)
        $newStart = $forcedStartDate ?? new \DateTime($data['project']['start_date']);

        // Calcul de l'intervalle entre la date min des tâches et la nouvelle date de début
        $interval = $minTaskStartDate->diff($newStart);

        $project->setStartDate($newStart);

        $originalEnd = new \DateTime($data['project']['end_date']);
        $project->setEndDate($originalEnd->add($interval));

        $this->em->persist($project);

        // Mapping ancien ID → nouvel objet Gtask
        $oldIdToNewGtask = [];

        foreach ($data['tasks'] as $taskData) {
            if (!isset($taskData['id'])) {
                throw new \RuntimeException("Tâche sans ID dans le JSON.");
            }

            $gtask = new Gtask();
            $gtask->setName($taskData['name']);
            $gtask->setStatus($taskData['status']);

            // Décalage des dates des tâches pour que la première commence à $newStart
            $startDate = new \DateTime($taskData['start_date']);
            $endDate = new \DateTime($taskData['end_date']);
            $gtask->setStartDate($startDate->add($interval));
            $gtask->setEndDate($endDate->add($interval));

            $gtask->setProject($project);

            $this->em->persist($gtask);

            $oldIdToNewGtask[$taskData['id']] = $gtask;
        }

        // Flush pour générer les IDs en base
        $this->em->flush();

        // Recréation des dépendances ManyToMany selon le mapping
        foreach ($data['dependencies'] as $dep) {
            $from = $oldIdToNewGtask[$dep['gtask_id']] ?? null;
            $to = $oldIdToNewGtask[$dep['depends_on_gtask_id']] ?? null;

            if (!$from || !$to) {
                throw new \RuntimeException("Dépendance invalide : tâche inconnue.");
            }

            // Ajoute la dépendance ManyToMany
            $from->addDependency($to);
            $this->em->persist($from);
        }

        $this->em->flush();

        return $project;
    }
}
