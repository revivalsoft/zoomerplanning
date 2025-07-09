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
// src/Controller/ProjectExportController.php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\GTask;
use App\Entity\Dependency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]
class ProjectExportController extends AbstractController
{

    #[Route('/project/export/list', name: 'project_export_list')]
    public function listProjects(EntityManagerInterface $em): Response
    {
        $projects = $em->getRepository(Project::class)->findAll();
        return $this->render('project_export/list.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project/{id}/export-model', name: 'project_export_model')]
    public function exportModel(int $id, EntityManagerInterface $em): Response
    {
        // 1. Charger le projet
        $project = $em->getRepository(Project::class)->find($id);
        if (!$project) {
            return $this->json(['error' => 'Projet non trouvé'], 404);
        }

        // 2. Charger les tâches et dépendances
        $tasks = $em->getRepository(GTask::class)->findBy(['project' => $project]);
        // $taskIds = array_map(fn($task) => $task->getId(), $tasks);

        // $dependencies = $em->getRepository(Dependency::class)
        //     ->createQueryBuilder('d')
        //     ->where('d.fromGtask IN (:taskIds)')
        //     ->setParameter('taskIds', $taskIds)
        //     ->getQuery()
        //     ->getResult();
        $dependencies = [];
        foreach ($tasks as $task) {
            foreach ($task->getDependencies() as $dep) {
                $dependencies[] = [
                    'gtask_id' => $task->getId(),
                    'depends_on_gtask_id' => $dep->getId(),
                ];
            }
        }

        // 3. Construire la structure du JSON
        $jsonArray = [
            'project' => [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'start_date' => $project->getStartDate()?->format('Y-m-d'),
                'end_date' => $project->getEndDate()?->format('Y-m-d'),
                'is_public' => false
            ],
            'tasks' => array_map(function ($task) {
                return [
                    'id' => $task->getId(),
                    'name' => $task->getName(),
                    'start_date' => $task->getStartDate()?->format('Y-m-d'),
                    'end_date' => $task->getEndDate()?->format('Y-m-d'),
                    'status' => 'waiting'
                ];
            }, $tasks),
            'dependencies' => $dependencies,
            // 'dependencies' => array_map(function ($dep) {
            //     return [
            //         'gtask_id' => $dep->getFromGtask()->getId(),           // tâche qui dépend
            //         'depends_on_gtask_id' => $dep->getToGtask()->getId(),  // dépend de cette tâche
            //     ];
            // }, $dependencies),
        ];

        // 4. Encoder en JSON (avec indentation pour la lisibilité)
        $jsonContent = json_encode($jsonArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // 5. Définir le chemin d’enregistrement
        $projectName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $project->getName());
        $filename = sprintf('%s_model.json', $projectName);
        $directory = $this->getParameter('kernel.project_dir') . '/var/';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $filepath = $directory . $filename;

        // 6. Sauvegarder le fichier sur le serveur
        file_put_contents($filepath, $jsonContent);

        // 7. Retourner le chemin du fichier ou un message de succès
        $this->addFlash(
            'success',
            'Modèle exporté avec succès sous : ' . basename($filepath)
        );

        return $this->redirectToRoute('project_export_list');
    }
}
