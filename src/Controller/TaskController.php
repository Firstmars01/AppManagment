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

    public function __construct(
        ProjectRepository $projectRepository,
        TaskRepository $taskRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->taskRepository = $taskRepository;
    }

    #[Route('/project/{slug}/task/{id}', name: 'app_task')]
    public function index(string $slug, string $id): Response
    {
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);

        if (!$project) {
            throw $this->createNotFoundException('This project does not exist');
        }

        $task = $this->taskRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('This task does not exist');
        }
        $taskBelongsToProject = false;

        if ($task->getMilestone() && $task->getMilestone()->getProject() === $project) {
            $taskBelongsToProject = true;
        } elseif ($task->getRequirement() && $task->getRequirement()->getProject() === $project) {
            $taskBelongsToProject = true;
        }

        if (!$taskBelongsToProject) {
            throw $this->createNotFoundException('This task does not belong to this project');
        }

        return $this->render('task/task.html.twig', [
            'project' => $project,
            'task' => $task,
        ]);
    }
}