<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    private ProjectRepository $projectRepository;
    private TaskRepository $taskRepository;

    // Inject repositories for projects and tasks
    public function __construct(
        ProjectRepository $projectRepository,
        TaskRepository $taskRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->taskRepository = $taskRepository;
    }

    // Route to view a specific task within a project
    #[Route('/{_locale<%app.supported_locales%>}/project/{slug}/task/{id}', name: 'app_task')]
    public function index(string $slug, string $id): Response
    {
        // Find the project by slug
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);

        // Throw 404 if project not found
        if (!$project) {
            throw $this->createNotFoundException('This project does not exist');
        }

        // Find the task by id
        $task = $this->taskRepository->find($id);

        // Throw 404 if task not found
        if (!$task) {
            throw $this->createNotFoundException('This task does not exist');
        }

        // Check if the task belongs to the project either via milestone or requirement
        $taskBelongsToProject = false;

        if ($task->getMilestone() && $task->getMilestone()->getProject() === $project) {
            $taskBelongsToProject = true;
        } elseif ($task->getRequirement() && $task->getRequirement()->getProject() === $project) {
            $taskBelongsToProject = true;
        }

        // Throw 404 if task does not belong to this project
        if (!$taskBelongsToProject) {
            throw $this->createNotFoundException('This task does not belong to this project');
        }

        // Render the task template and pass project and task data
        return $this->render('task/task.html.twig', [
            'project' => $project,
            'task' => $task,
        ]);
    }
}
