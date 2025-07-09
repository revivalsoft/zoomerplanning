<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Gtask;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]
class PublicGanttController extends AbstractController
{
    #[Route('/gantt/public', name: 'project_gantt_public_selector', methods: ['GET'])]
    public function publicGanttSelector(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findBy(['isPublic' => true]);

        return $this->render('project/public_selector.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/gantt/public/{id}', name: 'project_gantt_public', methods: ['GET'])]
    public function publicGantt(Project $project): Response
    {
        if (!$project->isPublic()) {
            throw $this->createAccessDeniedException('Ce projet n’est pas public.');
        }

        $gtasks = $project->getGtasks()->map(function (Gtask $task) {
            // Liste des dépendances (IDs)
            $dependencies = $task->getDependencies()
                ->map(fn($dep) => $dep->getId())
                ->toArray();

            // Couleur de la barre selon le statut
            $customClass = match ($task->getStatus()) {
                'waiting' => 'task-waiting',
                'in_progress' => 'task-inprogress',
                'done' => 'task-done',
                default => 'task-default',
            };

            return [
                'id' => $task->getId(),
                'name' => $task->getName(),
                'start' => $task->getStartDate()->format('Y-m-d'),
                'end' => $task->getEndDate()->format('Y-m-d'),
                'progress' => 100,
                'dependencies' => implode(',', $dependencies), // ✅ essentiel pour les flèches
                'custom_class' => $customClass,
            ];
        })->toArray();

        foreach ($project->getGtasks() as $gtask) {
            $gtask->setDependencyToIds(
                $gtask->getDependencies()->map(fn($d) => $d->getId())->toArray()
            );
        }

        return $this->render('project/gantt_public.html.twig', [
            'project' => $project,
            'gtasks' => $gtasks,
        ]);
    }
}
