<?php

namespace App\Tests\Controller;

use App\Controller\RequirementController;
use App\Entity\Project;
use App\Entity\Requirement;
use App\Repository\ProjectRepository;
use App\Repository\RequirementRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class RequirementControllerTest extends TestCase
{
    private ProjectRepository $projectRepository;
    private RequirementRepository $requirementRepository;
    private RequirementController $controller;

    protected function setUp(): void
    {
        // Create mock repositories
        $this->projectRepository = $this->createMock(ProjectRepository::class);
        $this->requirementRepository = $this->createMock(RequirementRepository::class);

        // Instantiate controller with mocked repositories
        $this->controller = new RequirementController(
            $this->projectRepository,
            $this->requirementRepository
        );
    }

    /**
     * Test happy flow: valid requirement belonging to the project displays correctly
     */
    public function testIndexWithValidRequirementAndProject(): void
    {
        $slug = 'my-project';
        $requirementId = Uuid::v4()->toRfc4122();

        // Create mock project
        $project = $this->createMock(Project::class);
        $project->method('getSlug')->willReturn($slug);

        // Create mock requirement that belongs to the project
        $requirement = $this->createMock(Requirement::class);
        $requirement->method('getId')->willReturn(Uuid::fromString($requirementId));
        $requirement->method('getProject')->willReturn($project);

        // Configure repositories
        $this->projectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => $slug])
            ->willReturn($project);

        $this->requirementRepository
            ->expects($this->once())
            ->method('find')
            ->with($requirementId)
            ->willReturn($requirement);

        // Mock controller
        $controller = $this->getMockBuilder(RequirementController::class)
            ->setConstructorArgs([$this->projectRepository, $this->requirementRepository])
            ->onlyMethods(['render'])
            ->getMock();

        // Assert render is called with correct template and parameters
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'requirement/requirement.html.twig',
                $this->callback(function ($params) use ($project, $requirement) {
                    $this->assertArrayHasKey('project', $params);
                    $this->assertArrayHasKey('requirement', $params);
                    $this->assertSame($project, $params['project']);
                    $this->assertSame($requirement, $params['requirement']);
                    return true;
                })
            )
            ->willReturn(new Response());

        // Execute
        $response = $controller->index($slug, $requirementId);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: requirement with complete data renders correctly
     */
    public function testIndexWithCompleteRequirementData(): void
    {
        $slug = 'complete-project';
        $requirementId = Uuid::v4()->toRfc4122();

        // Create mock project
        $project = $this->createMock(Project::class);
        $project->method('getSlug')->willReturn($slug);

        // Create mock requirement with complete data
        $requirement = $this->createMock(Requirement::class);
        $requirement->method('getId')->willReturn(Uuid::fromString($requirementId));
        $requirement->method('getProject')->willReturn($project);

        // Configure repositories
        $this->projectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => $slug])
            ->willReturn($project);

        $this->requirementRepository
            ->expects($this->once())
            ->method('find')
            ->with($requirementId)
            ->willReturn($requirement);

        // Mock controller
        $controller = $this->getMockBuilder(RequirementController::class)
            ->setConstructorArgs([$this->projectRepository, $this->requirementRepository])
            ->onlyMethods(['render'])
            ->getMock();

        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'requirement/requirement.html.twig',
                $this->callback(function ($params) use ($project, $requirement, $slug) {
                    // Verify both entities are passed correctly
                    $this->assertSame($project, $params['project']);
                    $this->assertSame($requirement, $params['requirement']);

                    // Verify we can call methods on the entities
                    $this->assertEquals($slug, $params['project']->getSlug());
                    $this->assertInstanceOf(Uuid::class, $params['requirement']->getId());

                    return true;
                })
            )
            ->willReturn(new Response('Rendered content', Response::HTTP_OK));

        // Execute
        $response = $controller->index($slug, $requirementId);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Test happy flow: multiple requirements from the same project
     */
    public function testIndexWithMultipleRequirementsFromSameProject(): void
    {
        $slug = 'shared-project';

        // Create one project
        $project = $this->createMock(Project::class);
        $project->method('getSlug')->willReturn($slug);

        // Test with 3 different requirements
        for ($i = 1; $i <= 3; $i++) {
            $requirementId = Uuid::v4()->toRfc4122();

            // Create fresh mocks for each iteration
            $projectRepo = $this->createMock(ProjectRepository::class);
            $requirementRepo = $this->createMock(RequirementRepository::class);

            // Create requirement
            $requirement = $this->createMock(Requirement::class);
            $requirement->method('getId')->willReturn(Uuid::fromString($requirementId));
            $requirement->method('getProject')->willReturn($project);

            // Configure repositories
            $projectRepo
                ->expects($this->once())
                ->method('findOneBy')
                ->with(['slug' => $slug])
                ->willReturn($project);

            $requirementRepo
                ->expects($this->once())
                ->method('find')
                ->with($requirementId)
                ->willReturn($requirement);

            // Mock controller
            $controller = $this->getMockBuilder(RequirementController::class)
                ->setConstructorArgs([$projectRepo, $requirementRepo])
                ->onlyMethods(['render'])
                ->getMock();

            $controller
                ->expects($this->once())
                ->method('render')
                ->willReturn(new Response());

            // Execute
            $response = $controller->index($slug, $requirementId);

            // Assert
            $this->assertInstanceOf(Response::class, $response);
        }
    }

    /**
     * Test happy flow: verify the template receives correct data structure
     */
    public function testIndexPassesCorrectDataStructureToTemplate(): void
    {
        $slug = 'test-project';
        $requirementId = Uuid::v4()->toRfc4122();

        $project = $this->createMock(Project::class);
        $requirement = $this->createMock(Requirement::class);
        $requirement->method('getProject')->willReturn($project);

        $this->projectRepository
            ->method('findOneBy')
            ->willReturn($project);

        $this->requirementRepository
            ->method('find')
            ->willReturn($requirement);

        // Mock controller
        $controller = $this->getMockBuilder(RequirementController::class)
            ->setConstructorArgs([$this->projectRepository, $this->requirementRepository])
            ->onlyMethods(['render'])
            ->getMock();

        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('requirement/requirement.html.twig'),
                $this->callback(function ($params) {
                    // Verify the data structure passed to template
                    $this->assertIsArray($params);
                    $this->assertCount(2, $params);
                    $this->assertArrayHasKey('project', $params);
                    $this->assertArrayHasKey('requirement', $params);

                    // Verify types
                    $this->assertInstanceOf(Project::class, $params['project']);
                    $this->assertInstanceOf(Requirement::class, $params['requirement']);

                    return true;
                })
            )
            ->willReturn(new Response());

        $response = $controller->index($slug, $requirementId);

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test the validation: requirement belongs to project relationship
     */
    public function testRequirementProjectRelationshipIsValidated(): void
    {
        $slug = 'validation-project';
        $requirementId = Uuid::v4()->toRfc4122();

        // Create project
        $project = $this->createMock(Project::class);
        $project->method('getSlug')->willReturn($slug);

        // Create requirement that belongs to this project
        $requirement = $this->createMock(Requirement::class);
        $requirement->method('getId')->willReturn(Uuid::fromString($requirementId));
        $requirement->method('getProject')->willReturn($project);

        $this->projectRepository
            ->method('findOneBy')
            ->willReturn($project);

        $this->requirementRepository
            ->method('find')
            ->willReturn($requirement);

        // Mock controller
        $controller = $this->getMockBuilder(RequirementController::class)
            ->setConstructorArgs([$this->projectRepository, $this->requirementRepository])
            ->onlyMethods(['render'])
            ->getMock();

        // The getProject method should be called to verify the relationship
        $requirement
            ->expects($this->atLeastOnce())
            ->method('getProject');

        $controller
            ->method('render')
            ->willReturn(new Response());

        $response = $controller->index($slug, $requirementId);

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: repositories are called with correct parameters
     */
    public function testRepositoriesAreCalledWithCorrectParameters(): void
    {
        $slug = 'parameter-test-project';
        $requirementId = Uuid::v4()->toRfc4122();

        $project = $this->createMock(Project::class);
        $requirement = $this->createMock(Requirement::class);
        $requirement->method('getProject')->willReturn($project);

        // Verify projectRepository is called with correct slug
        $this->projectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['slug' => $slug]))
            ->willReturn($project);

        // Verify requirementRepository is called with correct id
        $this->requirementRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($requirementId))
            ->willReturn($requirement);

        // Mock controller
        $controller = $this->getMockBuilder(RequirementController::class)
            ->setConstructorArgs([$this->projectRepository, $this->requirementRepository])
            ->onlyMethods(['render'])
            ->getMock();

        $controller
            ->method('render')
            ->willReturn(new Response());

        $response = $controller->index($slug, $requirementId);

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: different project slugs and requirement IDs
     */
    public function testIndexWithVariousSlugAndIdCombinations(): void
    {
        $combinations = [
            ['slug' => 'project-one', 'id' => Uuid::v4()->toRfc4122()],
            ['slug' => 'another-project', 'id' => Uuid::v4()->toRfc4122()],
            ['slug' => 'final-project', 'id' => Uuid::v4()->toRfc4122()],
        ];

        foreach ($combinations as $combo) {
            $projectRepo = $this->createMock(ProjectRepository::class);
            $requirementRepo = $this->createMock(RequirementRepository::class);

            $project = $this->createMock(Project::class);
            $requirement = $this->createMock(Requirement::class);
            $requirement->method('getProject')->willReturn($project);

            $projectRepo
                ->expects($this->once())
                ->method('findOneBy')
                ->willReturn($project);

            $requirementRepo
                ->expects($this->once())
                ->method('find')
                ->willReturn($requirement);

            $controller = $this->getMockBuilder(RequirementController::class)
                ->setConstructorArgs([$projectRepo, $requirementRepo])
                ->onlyMethods(['render'])
                ->getMock();

            $controller
                ->expects($this->once())
                ->method('render')
                ->willReturn(new Response());

            $response = $controller->index($combo['slug'], $combo['id']);

            $this->assertInstanceOf(Response::class, $response);
        }
    }
}