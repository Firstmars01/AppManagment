<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\MilestoneRepository;
use App\Repository\RequirementRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

 class TaskController extends AbstractController
{
    private ProjectRepository $projectRepository;
    private MilestoneRepository $milestoneRepository;
    private RequirementRepository $requirementRepository;
    private TaskRepository $taskRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        MilestoneRepository $milestoneRepository,
        RequirementRepository $requirementRepository,
        TaskRepository $taskRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->milestoneRepository = $milestoneRepository;
        $this->requirementRepository = $requirementRepository;
        $this->taskRepository = $taskRepository;
    }

    #[Route('/project/{slug}/milestone/{milestoneId}/requirement/{requirementId}/task/{id}', name: 'app_task')]
    public function index(string $slug, string $milestoneId, string $requirementId, string $id): Response
    {
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);

        if (!$project) {
            throw $this->createNotFoundException('Ce projet n\'existe pas');
        }

        $milestone = $this->milestoneRepository->find($milestoneId);

        if (!$milestone || $milestone->getProject() !== $project) {
            throw $this->createNotFoundException('Ce milestone n\'existe pas');
        }

        $requirement = $this->requirementRepository->find($requirementId);

        if (!$requirement || $requirement->getMilestone() !== $milestone) {
            throw $this->createNotFoundException('Ce requirement n\'existe pas');
        }

        $task = $this->taskRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Cette task n\'existe pas');
        }

        // Vérifier que la task appartient bien au requirement
        if ($task->getRequirement() !== $requirement) {
            throw $this->createNotFoundException('Cette task n\'appartient pas à ce requirement');
        }

        return $this->render('task/index.html.twig', [
            'project' => $project,
            'milestone' => $milestone,
            'requirement' => $requirement,
            'task' => $task,
        ]);
    }
}