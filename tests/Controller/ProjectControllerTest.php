<?php

namespace App\Tests\Controller;

use App\Controller\ProjectController;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProjectControllerTest extends TestCase
{
    private ProjectRepository $projectRepository;
    private ProjectController $controller;

    protected function setUp(): void
    {
        // Create mock repository
        $this->projectRepository = $this->createMock(ProjectRepository::class);

        // Instantiate controller with mocked repository
        $this->controller = new ProjectController($this->projectRepository);
    }

    /**
     * Test happy flow: homepage redirects to English locale
     */
    public function testIndexNoLocaleRedirectsToEnglish(): void
    {
        // Mock the controller to override redirectToRoute
        $controller = $this->getMockBuilder(ProjectController::class)
            ->setConstructorArgs([$this->projectRepository])
            ->onlyMethods(['redirectToRoute'])
            ->getMock();

        // Assert redirectToRoute is called with correct parameters
        $controller
            ->expects($this->once())
            ->method('redirectToRoute')
            ->with('project_list', ['_locale' => 'en'])
            ->willReturn(new RedirectResponse('/en/'));

        // Execute
        $response = $controller->indexNoLocale();

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * Test happy flow: project list displays all projects
     */
    public function testIndexDisplaysAllProjects(): void
    {
        // Create mock projects
        $project1 = $this->createMock(Project::class);
        $project1->method('getSlug')->willReturn('project-one');

        $project2 = $this->createMock(Project::class);
        $project2->method('getSlug')->willReturn('project-two');

        $projects = [$project1, $project2];

        // Configure repository to return projects
        $this->projectRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['createdAt' => 'DESC'])
            ->willReturn($projects);

        // Mock controller
        $controller = $this->getMockBuilder(ProjectController::class)
            ->setConstructorArgs([$this->projectRepository])
            ->onlyMethods(['render'])
            ->getMock();

        // Assert render is called with correct template and data
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'project/index.html.twig',
                $this->callback(function ($params) use ($projects) {
                    $this->assertArrayHasKey('projects', $params);
                    $this->assertSame($projects, $params['projects']);
                    $this->assertCount(2, $params['projects']);
                    return true;
                })
            )
            ->willReturn(new Response());

        // Execute
        $response = $controller->index();

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: project list with no projects returns empty array
     */
    public function testIndexWithNoProjectsReturnsEmptyArray(): void
    {
        // Configure repository to return empty array
        $this->projectRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['createdAt' => 'DESC'])
            ->willReturn([]);

        // Mock controller
        $controller = $this->getMockBuilder(ProjectController::class)
            ->setConstructorArgs([$this->projectRepository])
            ->onlyMethods(['render'])
            ->getMock();

        // Assert render is called with empty projects array
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'project/index.html.twig',
                $this->callback(function ($params) {
                    $this->assertArrayHasKey('projects', $params);
                    $this->assertEmpty($params['projects']);
                    return true;
                })
            )
            ->willReturn(new Response());

        // Execute
        $response = $controller->index();

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: show displays a single project by slug
     */
    public function testShowDisplaysProjectBySlug(): void
    {
        $slug = 'my-awesome-project';

        // Create mock project
        $project = $this->createMock(Project::class);
        $project->method('getSlug')->willReturn($slug);

        // Configure repository to return the project
        $this->projectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => $slug])
            ->willReturn($project);

        // Mock controller
        $controller = $this->getMockBuilder(ProjectController::class)
            ->setConstructorArgs([$this->projectRepository])
            ->onlyMethods(['render'])
            ->getMock();

        // Assert render is called with correct template and project
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'project/projectdetails.html.twig',
                $this->callback(function ($params) use ($project) {
                    $this->assertArrayHasKey('project', $params);
                    $this->assertSame($project, $params['project']);
                    return true;
                })
            )
            ->willReturn(new Response());

        // Execute
        $response = $controller->show($slug);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: show with different project slugs
     */
    public function testShowWithDifferentSlugs(): void
    {
        $slugs = ['project-alpha', 'project-beta', 'my-new-project'];

        foreach ($slugs as $slug) {
            // Create a fresh mock repository for each iteration
            $projectRepository = $this->createMock(ProjectRepository::class);

            // Create mock project for each slug
            $project = $this->createMock(Project::class);
            $project->method('getSlug')->willReturn($slug);

            // Configure repository
            $projectRepository
                ->expects($this->once())
                ->method('findOneBy')
                ->with(['slug' => $slug])
                ->willReturn($project);

            // Mock controller with fresh repository
            $controller = $this->getMockBuilder(ProjectController::class)
                ->setConstructorArgs([$projectRepository])
                ->onlyMethods(['render'])
                ->getMock();

            $controller
                ->expects($this->once())
                ->method('render')
                ->willReturn(new Response());

            // Execute
            $response = $controller->show($slug);

            // Assert
            $this->assertInstanceOf(Response::class, $response);
        }
    }

    /**
     * Test happy flow: project list is sorted by creation date descending
     */
    public function testIndexSortsProjectsByCreatedAtDesc(): void
    {
        $projects = [];

        // Configure repository with specific ordering expectation
        $this->projectRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(
                [],
                ['createdAt' => 'DESC'] // Verify the sorting parameter
            )
            ->willReturn($projects);

        // Mock controller
        $controller = $this->getMockBuilder(ProjectController::class)
            ->setConstructorArgs([$this->projectRepository])
            ->onlyMethods(['render'])
            ->getMock();

        $controller
            ->method('render')
            ->willReturn(new Response());

        // Execute
        $response = $controller->index();

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: project data structure passed to template
     */
    public function testShowPassesCorrectDataStructureToTemplate(): void
    {
        $slug = 'test-project';
        $project = $this->createMock(Project::class);

        $this->projectRepository
            ->method('findOneBy')
            ->willReturn($project);

        // Mock controller
        $controller = $this->getMockBuilder(ProjectController::class)
            ->setConstructorArgs([$this->projectRepository])
            ->onlyMethods(['render'])
            ->getMock();

        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('project/projectdetails.html.twig'),
                $this->callback(function ($params) {
                    // Verify data structure
                    $this->assertIsArray($params);
                    $this->assertCount(1, $params);
                    $this->assertArrayHasKey('project', $params);
                    $this->assertInstanceOf(Project::class, $params['project']);
                    return true;
                })
            )
            ->willReturn(new Response());

        // Execute
        $response = $controller->show($slug);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: multiple projects with complete data
     */
    public function testIndexWithMultipleCompleteProjects(): void
    {
        // Create multiple projects with various properties
        $projects = [];
        for ($i = 1; $i <= 5; $i++) {
            $project = $this->createMock(Project::class);
            $project->method('getSlug')->willReturn("project-$i");
            $projects[] = $project;
        }

        $this->projectRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($projects);

        // Mock controller
        $controller = $this->getMockBuilder(ProjectController::class)
            ->setConstructorArgs([$this->projectRepository])
            ->onlyMethods(['render'])
            ->getMock();

        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'project/index.html.twig',
                $this->callback(function ($params) {
                    $this->assertCount(5, $params['projects']);
                    return true;
                })
            )
            ->willReturn(new Response());

        // Execute
        $response = $controller->index();

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }
}