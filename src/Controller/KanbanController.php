<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/kanban')]
#[IsGranted(('ROLE_ADMIN'))]
class KanbanController extends AbstractController
{
    #[Route('', name: 'kanban_index')]
    public function index(TaskRepository $taskRepository)
    {
        $user = $this->getUser();

        $tasks = $taskRepository->findBy(['user' => $user], ['position' => 'ASC']);

        return $this->render('kanban/index.html.twig', [
            'tasks_by_column' => [
                'todo' => array_filter($tasks, fn($t) => $t->getColumn() === 'todo'),
                'in_progress' => array_filter($tasks, fn($t) => $t->getColumn() === 'in_progress'),
                'done' => array_filter($tasks, fn($t) => $t->getColumn() === 'done'),
            ],
        ]);
    }


    #[Route('/kanban/move', name: 'kanban_move', methods: ['POST'])]
    public function move(Request $request, EntityManagerInterface $em, TaskRepository $taskRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        foreach ($data['columns'] as $column => $taskIds) {
            foreach ($taskIds as $position => $taskId) {
                $task = $taskRepository->find($taskId);
                if ($task) {
                    $task->setColumn($column);
                    $task->setPosition($position);
                }
            }
        }

        $em->flush();

        return new JsonResponse(['status' => 'ok']);
    }


    #[Route('/create', name: 'kanban_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Task();
        $task->setUser($this->getUser());
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($task);
            $em->flush();
            return $this->redirectToRoute('kanban_index');
        }

        return $this->render('kanban/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => false,
        ]);
    }

    #[Route('/edit/{id}', name: 'kanban_edit')]
    public function edit(Task $task, Request $request, EntityManagerInterface $em): Response
    {
        if ($task->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('kanban_index');
        }

        return $this->render('kanban/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => true,
        ]);
    }

    #[Route('/delete/{id}', name: 'kanban_delete')]
    public function delete(Task $task, EntityManagerInterface $em): Response
    {
        if ($task->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($task);
        $em->flush();
        return $this->redirectToRoute('kanban_index');
    }
}
