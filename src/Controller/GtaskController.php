<?php

namespace App\Controller;

use App\Entity\Gtask;
use App\Entity\Project;
use App\Form\GtaskType;
use App\Repository\GtaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gtask')]
#[IsGranted(('ROLE_ADMIN'))]
class GtaskController extends AbstractController
{
    #[Route('/', name: 'gtask_index', methods: ['GET'])]
    public function index(GtaskRepository $gtaskRepository): Response
    {


        $user = $this->getUser();

        $gtasks = $gtaskRepository->findByUserProjects($user);

        return $this->render('gtask/index.html.twig', [
            'gtasks' => $gtasks,
        ]);
    }

    #[Route('/new/{project}', name: 'gtask_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Project $project, EntityManagerInterface $em): Response
    {
        $gtask = new Gtask();
        $gtask->setProject($project);

        $form = $this->createForm(GtaskType::class, $gtask);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($gtask);
            $em->flush();


            return $this->redirectToRoute('project_show', [
                'id' => $gtask->getProject()->getId()
            ]);
        }

        return $this->render('gtask/new.html.twig', [
            'gtask' => $gtask,
            'form' => $form,
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'gtask_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Gtask $gtask, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $project = $gtask->getProject();

        // Vérifie que le projet appartient bien à l'utilisateur connecté
        if ($project->getUser() !== $user) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier cette tâche.");
        }

        $form = $this->createForm(GtaskType::class, $gtask);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('project_show', [
                'id' => $project->getId()
            ]);
        }

        return $this->render('gtask/edit.html.twig', [
            'gtask' => $gtask,
            'form' => $form,
            'project' => $project,
        ]);
    }


    #[Route('/{id}', name: 'gtask_delete', methods: ['POST'])]
    public function delete(Request $request, Gtask $gtask, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $project = $gtask->getProject();

        // Vérifie que le projet appartient bien à l'utilisateur connecté
        if ($project->getUser() !== $user) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier cette tâche.");
        }


        if ($this->isCsrfTokenValid('delete' . $gtask->getId(), $request->request->get('_token'))) {
            $em->remove($gtask);
            $em->flush();
        }

        return $this->redirectToRoute('gtask_index');
    }


    #[Route('/update-dates/{id}', name: 'gtask_update_dates', methods: ['POST'])]
    public function updateDates(
        int $id,
        Request $request,
        GtaskRepository $gtaskRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            $gtask = $gtaskRepository->find($id);
            if (!$gtask) {
                return new JsonResponse(['success' => false, 'error' => 'Tâche non trouvée'], 404);
            }

            $data = json_decode($request->getContent(), true);
            if (!isset($data['start'], $data['end'])) {
                return new JsonResponse(['success' => false, 'error' => 'Données manquantes'], 400);
            }

            $newStart = new \DateTime($data['start']);
            $newEnd = new \DateTime($data['end']);

            $updated = false;
            if ($gtask->getStartDate()?->format('Y-m-d') !== $newStart->format('Y-m-d')) {
                $gtask->setStartDate($newStart);
                $updated = true;
            }
            if ($gtask->getEndDate()?->format('Y-m-d') !== $newEnd->format('Y-m-d')) {
                $gtask->setEndDate($newEnd);
                $updated = true;
            }

            if ($updated) {
                $em->flush();
            }

            return new JsonResponse(['success' => true, 'updated' => $updated]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Exception',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/gtask/manage-dependencies/{id}', name: 'dependency_manage', methods: ['POST'])]
    public function manageDependencies(
        int $id,
        Request $request,
        GtaskRepository $gtaskRepository,
        EntityManagerInterface $em
    ): Response {
        $gtask = $gtaskRepository->find($id);
        if (!$gtask) {
            throw $this->createNotFoundException('Tâche introuvable');
        }

        $submittedIds = array_map('intval', $request->request->all('dependencies', []));
        $allGtasks = $gtaskRepository->findBy(['project' => $gtask->getProject()]);
        $gtaskMap = [];
        foreach ($allGtasks as $t) {
            $gtaskMap[$t->getId()] = $t;
        }

        // Suppression des dépendances non cochées
        foreach ($gtask->getDependencies()->toArray() as $existingDep) {
            if (!in_array($existingDep->getId(), $submittedIds)) {
                $gtask->removeDependency($existingDep);
            }
        }

        // Ajout avec vérification stricte de cycle via graphe mémoire
        foreach ($submittedIds as $depId) {
            if ($depId === $gtask->getId() || !isset($gtaskMap[$depId])) {
                continue;
            }

            $depGtask = $gtaskMap[$depId];

            if ($this->detectCycleInMemoryGraph($gtask, $depGtask, $allGtasks)) {
                $this->addFlash('danger', "Impossible : {$gtask->getName()} → {$depGtask->getName()} créerait une boucle ❌");
                continue;
            }

            if (!$gtask->getDependencies()->contains($depGtask)) {
                $gtask->addDependency($depGtask);
            }
        }

        $em->flush();
        //$this->addFlash('success', 'Dépendances mises à jour ✅');

        return $this->redirectToRoute('project_gantt', ['id' => $gtask->getProject()->getId()]);
    }

    private function detectCycleInMemoryGraph(Gtask $from, Gtask $to, iterable $allGtasks): bool
    {
        // Construire le graphe { id => [ids dépendants] }
        $graph = [];
        foreach ($allGtasks as $gtask) {
            $graph[$gtask->getId()] = [];
            foreach ($gtask->getDependencies() as $dep) {
                $graph[$gtask->getId()][] = $dep->getId();
            }
        }

        // Simuler l'ajout de from -> to
        $graph[$from->getId()][] = $to->getId();

        // DFS pour détecter si un chemin revient à from
        $visited = [];
        $gstack = [$from->getId()];

        while (!empty($gstack)) {
            $current = array_pop($gstack);
            if (in_array($current, $visited)) continue;
            $visited[] = $current;

            foreach ($graph[$current] ?? [] as $neighbor) {
                if ($neighbor === $from->getId()) {
                    return true; // boucle détectée
                }
                $gstack[] = $neighbor;
            }
        }

        return false;
    }
}
