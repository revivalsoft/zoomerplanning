<?php
//GANTT

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Repository\GtaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/project')]
#[IsGranted(('ROLE_ADMIN'))]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        $user = $this->getUser();

        $projects = $projectRepository->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $project->setUser($this->getUser());
            $em->persist($project);
            $em->flush();
            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/new.html.twig', [
            'form' => $form->createView(),

        ]);
    }

    #[Route('/{id}/edit', name: 'project_edit', methods: ['GET', 'POST'])]
    public function edit(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($project->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à ce projet.');
        }

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            //return $this->redirectToRoute('project_index');
            return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'project_delete', methods: ['POST'])]
    public function delete(Project $project, Request $request, EntityManagerInterface $em): Response
    {

        if ($project->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $project->getId(), $request->request->get('_token'))) {
            $em->remove($project);
            $em->flush();
        }

        return $this->redirectToRoute('project_index');
    }

    #[Route('/{id}/gantt', name: 'project_gantt', methods: ['GET'])]
    public function gantt(Project $project, GtaskRepository $gtaskRepository): Response
    {

        if ($project->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $allGtasks = $project->getGtasks();

        // Construction du graphe { id => [ids dépendants] }
        $graph = [];
        foreach ($allGtasks as $gtask) {
            $graph[$gtask->getId()] = [];
            foreach ($gtask->getDependencies() as $dep) {
                $graph[$gtask->getId()][] = $dep->getId();
            }
        }

        $descendantsMap = [];
        $ancestorsMap = [];

        foreach ($allGtasks as $gtask) {
            $id = $gtask->getId();

            // Descendants : parcours descendant
            $descendants = [];
            $stack = [$id];
            while (!empty($stack)) {
                $current = array_pop($stack);
                foreach ($graph[$current] ?? [] as $childId) {
                    if (!in_array($childId, $descendants)) {
                        $descendants[] = $childId;
                        $stack[] = $childId;
                    }
                }
            }
            $descendantsMap[$id] = $descendants;

            // Ancêtres : parcours inverse
            $ancestors = [];
            $stack = [$id];
            while (!empty($stack)) {
                $current = array_pop($stack);
                foreach ($graph as $parentId => $children) {
                    if (in_array($current, $children) && !in_array($parentId, $ancestors)) {
                        $ancestors[] = $parentId;
                        $stack[] = $parentId;
                    }
                }
            }
            $ancestorsMap[$id] = $ancestors;
        }

        // Affecter à chaque tâche les IDs interdits (ancêtres + descendants)
        foreach ($allGtasks as $gtask) {
            $id = $gtask->getId();
            $gtask->setDependencyToIds(
                $gtask->getDependencies()->map(fn($d) => $d->getId())->toArray()
            );
            $gtask->setCircularToIds(array_unique(array_merge(
                $descendantsMap[$id] ?? [],
                $ancestorsMap[$id] ?? []
            )));
        }

        return $this->render('project/gantt.html.twig', [
            'project' => $project,

        ]);
    }

    #[Route('/{id}', name: 'project_show', methods: ['GET'])]
    public function show(Project $project, Request $request, EntityManagerInterface $em): Response
    {

        if ($project->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }
}
