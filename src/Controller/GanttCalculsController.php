<?php

namespace App\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Gtask;
use App\Entity\GtaskResource;
use App\Repository\ParamRepository;
use Doctrine\DBAL\ParameterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GanttCalculsController extends AbstractController
{
    #[Route('/gantt/calculs', name: 'app_gantt_calculs')]
    public function index(): Response
    {
        return $this->render('gantt_calculs/index.html.twig', [
            'controller_name' => 'GanttCalculsController',
        ]);
    }

    #[Route('/selection-projet', name: 'selection_projet')]
    public function selectionProjet(EntityManagerInterface $em): Response
    {
        $projets = $em->getRepository(Project::class)->findAll();

        return $this->render('gantt_calculs/select_project.html.twig', [
            'projets' => $projets,
        ]);
    }

    #[Route('/calculer-heures/{id}', name: 'calcul_heures_par_task')]
    public function calculHeuresParTask(
        int $id, // project_id
        EntityManagerInterface $em,
        ParamRepository $paramRepository,
    ): Response {
        // Récupération des tâches du projet
        $tasks = $em->getRepository(Gtask::class)->findBy(['project' => $id]);

        $param = $paramRepository->find(1);
        $lignePublic = $param->getPublic();

        $resultats = [];

        foreach ($tasks as $task) {
            $gtaskId = $task->getId();
            $start = $task->getStartDate();
            $end = $task->getEndDate();

            // Récupérer les ressources liées à la tâche
            $gtaskResources = $em->getRepository(GtaskResource::class)->findBy(['gtask' => $gtaskId]);

            $totalHeures = 0;

            foreach ($gtaskResources as $gtaskResource) {
                $ressourceId = $gtaskResource->getRessource()->getId();
                $conn = $em->getConnection();
                $sql = "
                SELECT g.*, p.heure, p.minute
                FROM gestion g
                JOIN plage p ON g.plage_id = p.id
                WHERE g.ressource_id = :ressourceId
                AND g.line <= $lignePublic
                AND g.date BETWEEN :start AND :end
            ";

                // Doctrine DBAL 4 : on utilise prepare() + executeQuery() ou executeStatement()
                $result = $conn->executeQuery($sql, [
                    'ressourceId' => $ressourceId,
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                ], [
                    'ressourceId' => ParameterType::INTEGER,
                    'start' => ParameterType::STRING,
                    'end' => ParameterType::STRING,
                ]);

                $rows = $result->fetchAllAssociative();

                foreach ($rows as $row) {
                    $heures = (int)$row['heure'];
                    $minutes = (int)$row['minute'];
                    $totalHeures += $heures + $minutes / 60;
                }
            }

            $resultats[] = [
                'task' => $task->getName(),
                'heures' => round($totalHeures, 2),
            ];
        }

        return $this->render('gantt_calculs/resultats.html.twig', [
            'resultats' => $resultats
        ]);
    }
}
