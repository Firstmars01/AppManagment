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
    // Inject repositories for projects, tasks, and milestones
    public function __construct(
        private ProjectRepository $projectRepository,
        private TaskRepository $taskRepository,
        private MilestoneRepository $milestoneRepository
    ) {}

    // Route for the dashboard page
    #[Route('/{_locale<%app.supported_locales%>}/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $user = $this->getUser(); // Get the currently logged-in user

        // Deny access if no user is logged in
        if (!$user) {
            throw $this->createAccessDeniedException("You are connected.");
        }

        // Get projects owned by the user, sorted by creation date descending
        $projects = $this->projectRepository->findBy(
            ['owner' => $user],
            ['createdAt' => 'DESC']
        );

        // Get tasks managed by the user, sorted by planned start date ascending
        $tasks = $this->taskRepository->findBy(
            ['manager' => $user],
            ['plannedStartDate' => 'ASC']
        );

        // Get milestones managed by the user, sorted by planned start date ascending
        $milestones = $this->milestoneRepository->findBy(
            ['manager' => $user],
            ['plannedStartDate' => 'ASC']
        );

        // Prepare project requirements for tasks that have milestones
        $projectRequirements = [];
        foreach ($tasks as $task) {
            if ($task->getMilestone() && $task->getMilestone()->getProject()) {
                $project = $task->getMilestone()->getProject();
                $projectSlug = $project->getSlug();

                // Add project requirements to the array if not already added
                if (!isset($projectRequirements[$projectSlug])) {
                    $projectRequirements[$projectSlug] = $project->getRequirements()->toArray();
                }
            }
        }

        // Render the dashboard template and pass data
        return $this->render('dashboard/dashboard.html.twig', [
            'projects' => $projects,
            'tasks' => $tasks,
            'milestones' => $milestones,
            'projectRequirements' => $projectRequirements,
        ]);
    }
}
