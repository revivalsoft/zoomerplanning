<?php
// src/Controller/GanttResourceController.php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Gtask;
use App\Entity\Ressource;
use App\Entity\GtaskResource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class GanttResourceController extends AbstractController
{
    #[Route('/gantt', name: 'gantt_affectations')]
    #[IsGranted(('ROLE_ADMIN'))]

    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // 1) Récupérer l'utilisateur
        $user = $this->getUser();
        if (! $user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // 2) Récupérer les projets de l'utilisateur
        $projects = $em->getRepository(Project::class)
            ->findBy(['user' => $user]);

        $projectId = $request->query->get('project');
        $selectedProject = null;
        $tasks     = [];
        $ressources = [];

        if ($projectId) {
            $selectedProject = $em->getRepository(Project::class)->find($projectId);
            $tasks = $em->getRepository(Gtask::class)
                ->findBy(['project' => $selectedProject]);

            // 3) Lire directement la table user_groupe pour obtenir les IDs

            $user = $this->getUser();

            if (!$user instanceof \App\Entity\Ressource) {
                throw new \LogicException('L’utilisateur connecté n’est pas une Ressource.');
            }
            $conn = $em->getConnection();
            $groupIds = $conn
                ->executeQuery(
                    'SELECT groupe_id FROM admin_groupe WHERE ressource_id = :uid',
                    ['uid' => $user->getId()]
                )
                ->fetchFirstColumn();  // tableau d’IDs (peut être vide)

            if (!empty($groupIds)) {
                // 4) Ne récupérer que les Ressource liées à ces groupes
                $qb = $em->createQueryBuilder()
                    ->select('r')
                    ->from(Ressource::class, 'r')
                    ->join('r.groupe', 'g')        // propriété many-to-many "groupe"
                    ->andWhere('g.id IN (:groupIds)')
                    ->setParameter('groupIds', $groupIds);
                $ressources = $qb->getQuery()->getResult();
            }
        }
        // Traitement de l'affectation via POST
        if ($request->isMethod('POST') && $selectedProject) {
            foreach ($tasks as $task) {
                $ids = $request->request->all('ressources_' . $task->getId()) ?: [];
                // Supprime les affectations existantes
                foreach ($task->getGtaskResources() as $gr) {
                    $em->remove($gr);
                }
                // Ajoute les nouvelles affectations
                foreach ($ids as $rid) {
                    $ressource = $em->getRepository(Ressource::class)->find($rid);
                    if ($ressource) {
                        $gr = new GtaskResource();
                        $gr->setGtask($task);
                        $gr->setRessource($ressource);
                        $em->persist($gr);
                    }
                }
            }
            $em->flush();
            $this->addFlash('success', 'Affectations mises à jour');
            return $this->redirectToRoute('gantt_affectations', ['project' => $projectId]);
        }

        return $this->render('gantt/affectations.html.twig', [
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'tasks' => $tasks,
            'ressources' => $ressources,
        ]);
    }
}
