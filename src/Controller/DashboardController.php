<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\MilestoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private TaskRepository $taskRepository,
        private MilestoneRepository $milestoneRepository
    ) {}

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }

        $projects = $this->projectRepository->findBy(
            ['owner' => $user],
            ['createdAt' => 'DESC']
        );

        $tasks = $this->taskRepository->findBy(
            ['manager' => $user],
            ['plannedStartDate' => 'ASC']
        );

        $milestones = $this->milestoneRepository->findBy(
            ['manager' => $user],
            ['plannedStartDate' => 'ASC']
        );

        // Créer un map des requirements par projet (utilise le slug comme clé)
        $projectRequirements = [];
        foreach ($tasks as $task) {
            if ($task->getMilestone() && $task->getMilestone()->getProject()) {
                $project = $task->getMilestone()->getProject();
                $projectSlug = $project->getSlug();

                if (!isset($projectRequirements[$projectSlug])) {
                    $projectRequirements[$projectSlug] = $project->getRequirements()->toArray();
                }
            }
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'projects' => $projects,
            'tasks' => $tasks,
            'milestones' => $milestones,
            'projectRequirements' => $projectRequirements,
        ]);
    }
}