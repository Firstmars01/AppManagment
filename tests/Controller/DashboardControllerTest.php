<?php

namespace App\Tests\Controller;

use App\Controller\DashboardController;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\Milestone;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\MilestoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DashboardControllerTest extends TestCase
{
    private ProjectRepository $projectRepository;
    private TaskRepository $taskRepository;
    private MilestoneRepository $milestoneRepository;
    private DashboardController $controller;

    protected function setUp(): void
    {
        // Create mocks for repositories
        $this->projectRepository = $this->createMock(ProjectRepository::class);
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->milestoneRepository = $this->createMock(MilestoneRepository::class);

        // Instantiate the controller with mocked repositories
        $this->controller = new DashboardController(
            $this->projectRepository,
            $this->taskRepository,
            $this->milestoneRepository
        );
    }

    /**
     * Test happy flow: authenticated user accesses dashboard with projects, tasks, and milestones
     */
    public function testIndexWithAuthenticatedUserReturnsProjects(): void
    {
        // Create a mock user
        $user = $this->createMock(User::class);

        // Create mock projects
        $project1 = $this->createMock(Project::class);
        $project1->method('getSlug')->willReturn('project-1');
        $project1->method('getRequirements')->willReturn(new ArrayCollection(['req1', 'req2']));

        $project2 = $this->createMock(Project::class);
        $project2->method('getSlug')->willReturn('project-2');
        $project2->method('getRequirements')->willReturn(new ArrayCollection(['req3']));

        $projects = [$project1, $project2];

        // Create mock milestone
        $milestone = $this->createMock(Milestone::class);
        $milestone->method('getProject')->willReturn($project1);

        // Create mock tasks
        $task1 = $this->createMock(Task::class);
        $task1->method('getMilestone')->willReturn($milestone);

        $task2 = $this->createMock(Task::class);
        $task2->method('getMilestone')->willReturn(null);

        $tasks = [$task1, $task2];

        // Create mock milestones
        $milestones = [$milestone];

        // Configure repository mocks to return data
        $this->projectRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['owner' => $user], ['createdAt' => 'DESC'])
            ->willReturn($projects);

        $this->taskRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['manager' => $user], ['plannedStartDate' => 'ASC'])
            ->willReturn($tasks);

        $this->milestoneRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['manager' => $user], ['plannedStartDate' => 'ASC'])
            ->willReturn($milestones);

        // Mock the getUser method to return our mock user
        $controller = $this->getMockBuilder(DashboardController::class)
            ->setConstructorArgs([
                $this->projectRepository,
                $this->taskRepository,
                $this->milestoneRepository
            ])
            ->onlyMethods(['getUser', 'render'])
            ->getMock();

        $controller->method('getUser')->willReturn($user);

        // Mock the render method to capture the parameters
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'dashboard/dashboard.html.twig',
                $this->callback(function ($params) use ($projects, $tasks, $milestones) {
                    // Assert that all expected data is passed to the template
                    $this->assertArrayHasKey('projects', $params);
                    $this->assertArrayHasKey('tasks', $params);
                    $this->assertArrayHasKey('milestones', $params);
                    $this->assertArrayHasKey('projectRequirements', $params);

                    $this->assertSame($projects, $params['projects']);
                    $this->assertSame($tasks, $params['tasks']);
                    $this->assertSame($milestones, $params['milestones']);

                    // Check that projectRequirements contains the expected project
                    $this->assertArrayHasKey('project-1', $params['projectRequirements']);
                    $this->assertIsArray($params['projectRequirements']['project-1']);

                    return true;
                })
            )
            ->willReturn(new Response());

        // Execute the controller action
        $response = $controller->index();

        // Assert the response is successful
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: user has no projects, tasks, or milestones
     */
    public function testIndexWithAuthenticatedUserHavingNoData(): void
    {
        // Create a mock user
        $user = $this->createMock(User::class);

        // Configure repositories to return empty arrays
        $this->projectRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['owner' => $user], ['createdAt' => 'DESC'])
            ->willReturn([]);

        $this->taskRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['manager' => $user], ['plannedStartDate' => 'ASC'])
            ->willReturn([]);

        $this->milestoneRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['manager' => $user], ['plannedStartDate' => 'ASC'])
            ->willReturn([]);

        // Mock the controller
        $controller = $this->getMockBuilder(DashboardController::class)
            ->setConstructorArgs([
                $this->projectRepository,
                $this->taskRepository,
                $this->milestoneRepository
            ])
            ->onlyMethods(['getUser', 'render'])
            ->getMock();

        $controller->method('getUser')->willReturn($user);

        // Mock the render method
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'dashboard/dashboard.html.twig',
                $this->callback(function ($params) {
                    // Assert empty arrays are passed
                    $this->assertEmpty($params['projects']);
                    $this->assertEmpty($params['tasks']);
                    $this->assertEmpty($params['milestones']);
                    $this->assertEmpty($params['projectRequirements']);

                    return true;
                })
            )
            ->willReturn(new Response());

        // Execute the controller action
        $response = $controller->index();

        // Assert the response is successful
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: multiple tasks with different milestone/project associations
     */
    public function testIndexCorrectlyGroupsProjectRequirements(): void
    {
        $user = $this->createMock(User::class);

        // Create mock projects
        $project1 = $this->createMock(Project::class);
        $project1->method('getSlug')->willReturn('project-1');
        $project1->method('getRequirements')->willReturn(new ArrayCollection(['req1', 'req2']));

        $project2 = $this->createMock(Project::class);
        $project2->method('getSlug')->willReturn('project-2');
        $project2->method('getRequirements')->willReturn(new ArrayCollection(['req3', 'req4']));

        // Create milestones
        $milestone1 = $this->createMock(Milestone::class);
        $milestone1->method('getProject')->willReturn($project1);

        $milestone2 = $this->createMock(Milestone::class);
        $milestone2->method('getProject')->willReturn($project2);

        // Create tasks
        $task1 = $this->createMock(Task::class);
        $task1->method('getMilestone')->willReturn($milestone1);

        $task2 = $this->createMock(Task::class);
        $task2->method('getMilestone')->willReturn($milestone1); // Same project

        $task3 = $this->createMock(Task::class);
        $task3->method('getMilestone')->willReturn($milestone2); // Different project

        $tasks = [$task1, $task2, $task3];

        // Configure repositories
        $this->projectRepository->method('findBy')->willReturn([]);
        $this->taskRepository->method('findBy')->willReturn($tasks);
        $this->milestoneRepository->method('findBy')->willReturn([]);

        // Mock controller
        $controller = $this->getMockBuilder(DashboardController::class)
            ->setConstructorArgs([
                $this->projectRepository,
                $this->taskRepository,
                $this->milestoneRepository
            ])
            ->onlyMethods(['getUser', 'render'])
            ->getMock();

        $controller->method('getUser')->willReturn($user);

        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'dashboard/dashboard.html.twig',
                $this->callback(function ($params) {
                    $projectRequirements = $params['projectRequirements'];

                    // Should have 2 distinct projects
                    $this->assertCount(2, $projectRequirements);
                    $this->assertArrayHasKey('project-1', $projectRequirements);
                    $this->assertArrayHasKey('project-2', $projectRequirements);

                    // Check requirements arrays
                    $this->assertCount(2, $projectRequirements['project-1']);
                    $this->assertCount(2, $projectRequirements['project-2']);

                    return true;
                })
            )
            ->willReturn(new Response());

        $response = $controller->index();
        $this->assertInstanceOf(Response::class, $response);
    }
}