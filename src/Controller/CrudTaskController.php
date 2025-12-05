<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TasksType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Base route for this controller with locale support
#[Route('/{_locale<%app.supported_locales%>}/crud/task')]
// Only users with ROLE_USER can access this controller
#[IsGranted('ROLE_USER')]
class CrudTaskController extends AbstractController
{
    // Route to list all tasks
    #[Route(name: 'app_crud_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        // Render index template and pass all tasks
        return $this->render('crud_task/index.html.twig', [
            'tasks' => $taskRepository->findAll(),
        ]);
    }

    // Route to create a new task
    #[Route('/new', name: 'app_crud_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task(); // Create new Task entity
        $form = $this->createForm(TasksType::class, $task); // Create form
        $form->handleRequest($request); // Handle form submission

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task); // Save task
            $entityManager->flush();

            // Redirect to task index after creation
            return $this->redirectToRoute('app_crud_task_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the new task form template
        return $this->render('crud_task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    // Route to show a specific task
    #[Route('/{id}', name: 'app_crud_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        // Render show template and pass the task
        return $this->render('crud_task/show.html.twig', [
            'task' => $task,
        ]);
    }

    // Route to edit an existing task
    #[Route('/{id}/edit', name: 'app_crud_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TasksType::class, $task); // Create form pre-filled with task data
        $form->handleRequest($request); // Handle form submission

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Save changes to the database

            // Redirect to task index after editing
            return $this->redirectToRoute('app_crud_task_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render edit form template
        return $this->render('crud_task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    // Route to delete a task
    #[Route('/{id}', name: 'app_crud_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        // Check CSRF token validity
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task); // Remove task from database
            $entityManager->flush();
        }

        // Redirect to task index after deletion
        return $this->redirectToRoute('app_crud_task_index', [], Response::HTTP_SEE_OTHER);
    }
}
