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
namespace App\Controller;

use App\Entity\Project;
use App\Entity\GtaskRaci;
use App\Repository\ProjectRepository;
use App\Repository\GtaskRepository;
use App\Repository\RessourceRepository;
use App\Repository\GtaskRaciRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class RaciController extends AbstractController
{
    #[Route('/raci/select', name: 'raci_select')]
    public function selectProject(ProjectRepository $projectRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $projects = $projectRepo->findBy(['user' => $user]);

        return $this->render('raci/select_project.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project/{id}/raci', name: 'project_raci')]
    public function show(
        Project $project,
        GtaskRepository $gtaskRepo,
        RessourceRepository $ressourceRepo,
        GtaskRaciRepository $raciRepo
    ): Response {
        $user = $this->getUser();
        if ($project->getUser() !== $user) {
            throw $this->createAccessDeniedException('Projet non accessible.');
        }

        $tasks = $gtaskRepo->findBy(['project' => $project]);
        //$ressources = $ressourceRepo->findAll();
        $ressources = $ressourceRepo->findByProject($project);

        $matrice = [];
        $ressourcesParTask = [];

        foreach ($tasks as $task) {
            // Récupérer les ressources liées à la tâche
            // Ici on suppose que ta tâche a une méthode getGtaskResources() 
            // qui retourne les entités gtask_resource liées
            $linkedGtaskResources = method_exists($task, 'getGtaskResources') ? $task->getGtaskResources() : [];

            // Extraire les ids des ressources liées
            $linkedIds = [];
            foreach ($linkedGtaskResources as $gr) {
                $linkedIds[] = $gr->getRessource()->getId();
            }
            $ressourcesParTask[$task->getId()] = $linkedIds;

            foreach ($ressources as $res) {
                if (in_array($res->getId(), $linkedIds)) {
                    $raci = $raciRepo->findOneBy(['gtask' => $task, 'ressource' => $res]);
                    $matrice[$task->getId()][$res->getId()] = $raci?->getRole() ?? 'R'; // défaut "R"
                } else {
                    $matrice[$task->getId()][$res->getId()] = null; // pas liée => pas de select
                }
            }
        }

        return $this->render('raci/matrix.html.twig', [
            'project' => $project,
            'project_id' => $project->getId(),
            'tasks' => $tasks,
            'ressources' => $ressources,
            'matrice' => $matrice,
            'ressourcesParTask' => $ressourcesParTask,
        ]);
    }

    #[Route('/project/{id}/raci/update', name: 'project_raci_update', methods: ['POST'])]
    public function update(
        Project $project,
        Request $request,
        GtaskRepository $gtaskRepo,
        RessourceRepository $ressourceRepo,
        GtaskRaciRepository $raciRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        if ($project->getUser() !== $user) {
            throw $this->createAccessDeniedException('Projet non accessible.');
        }

        $roles = $request->request->all('roles');

        foreach ($roles as $taskId => $ressources) {
            $task = $gtaskRepo->find($taskId);
            if (!$task) continue;

            foreach ($ressources as $resId => $role) {
                $ressource = $ressourceRepo->find($resId);
                if (!$ressource) continue;

                $existing = $raciRepo->findOneBy(['gtask' => $task, 'ressource' => $ressource]);

                if ($role === '') {
                    if ($existing) $em->remove($existing);
                    continue;
                }

                if (!$existing) {
                    $existing = new GtaskRaci();
                    $existing->setGtask($task);
                    $existing->setRessource($ressource);
                }

                $existing->setRole($role);
                $em->persist($existing);
            }
        }

        $em->flush();
        // 💡 Flash message
        $this->addFlash('success', 'La matrice RACIE a bien été enregistrée.');

        return $this->redirectToRoute('project_raci', ['id' => $project->getId()]);
    }

    #[Route('/project/{id}/raci/print', name: 'project_raci_print')]
    public function print(
        Project $project,
        GtaskRepository $gtaskRepo,
        RessourceRepository $ressourceRepo,
        GtaskRaciRepository $raciRepo
    ): Response {
        $user = $this->getUser();
        if ($project->getUser() !== $user) {
            throw $this->createAccessDeniedException('Projet non accessible.');
        }

        $tasks = $gtaskRepo->findBy(['project' => $project]);
        //$ressources = $ressourceRepo->findAll();
        $ressources = $ressourceRepo->findByProject($project);

        $matrice = [];
        foreach ($tasks as $task) {
            foreach ($ressources as $res) {
                $raci = $raciRepo->findOneBy(['gtask' => $task, 'ressource' => $res]);
                $matrice[$task->getId()][$res->getId()] = $raci?->getRole() ?? '';
            }
        }

        //$isLargeMatrix = count($ressources) > 10 || count($tasks) > 10;
        $isLargeMatrix = count($ressources) > 10;

        return $this->render('raci/print.html.twig', [
            'project' => $project,
            'tasks' => $tasks,
            'ressources' => $ressources,
            'matrice' => $matrice,
            'isLargeMatrix' => $isLargeMatrix,
        ]);
    }
}
