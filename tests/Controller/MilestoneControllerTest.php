<?php

namespace App\Tests\Controller;

use App\Controller\MilestoneController;
use App\Entity\Milestone;
use App\Entity\Project;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class MilestoneControllerTest extends TestCase
{
    private MilestoneController $controller;

    protected function setUp(): void
    {
        $this->controller = new MilestoneController();
    }

    /**
     * Test happy flow: milestone belongs to the project and page renders correctly
     */
    public function testIndexWithValidMilestoneAndProject(): void
    {
        // Create mock project
        $project = $this->createMock(Project::class);
        $project->method('getSlug')->willReturn('my-project');

        // Create mock milestone that belongs to the project
        $milestoneId = Uuid::v4();
        $milestone = $this->createMock(Milestone::class);
        $milestone->method('getId')->willReturn($milestoneId);
        $milestone->method('getProject')->willReturn($project);

        // Mock the controller to override the render method
        $controller = $this->getMockBuilder(MilestoneController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Assert render is called with correct template and parameters
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'milestone/milestone.html.twig',
                $this->callback(function ($params) use ($project, $milestone) {
                    $this->assertArrayHasKey('project', $params);
                    $this->assertArrayHasKey('milestone', $params);
                    $this->assertSame($project, $params['project']);
                    $this->assertSame($milestone, $params['milestone']);
                    return true;
                })
            )
            ->willReturn(new Response());

        // Execute the controller action
        $response = $controller->index($project, $milestone);

        // Assert response is valid
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test happy flow: milestone with all data populated renders correctly
     */
    public function testIndexWithFullyPopulatedMilestone(): void
    {
        // Create mock project with full data
        $project = $this->createMock(Project::class);
        $project->method('getSlug')->willReturn('complete-project');

        // Create mock milestone with full data
        $milestoneId = Uuid::v4();
        $milestone = $this->createMock(Milestone::class);
        $milestone->method('getId')->willReturn($milestoneId);
        $milestone->method('getProject')->willReturn($project);

        // Mock controller
        $controller = $this->getMockBuilder(MilestoneController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'milestone/milestone.html.twig',
                $this->callback(function ($params) use ($project, $milestone) {
                    // Verify both entities are passed correctly
                    $this->assertSame($project, $params['project']);
                    $this->assertSame($milestone, $params['milestone']);

                    // Verify we can call methods on the entities
                    $this->assertEquals('complete-project', $params['project']->getSlug());
                    $this->assertInstanceOf(Uuid::class, $params['milestone']->getId());

                    return true;
                })
            )
            ->willReturn(new Response('Rendered content', Response::HTTP_OK));

        // Execute
        $response = $controller->index($project, $milestone);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Test happy flow: multiple milestones can be accessed for the same project
     */
    public function testIndexWithDifferentMilestonesOfSameProject(): void
    {
        // Create one project
        $project = $this->createMock(Project::class);
        $project->method('getSlug')->willReturn('shared-project');

        // Create two different milestones for the same project
        $milestoneId1 = Uuid::v4();
        $milestone1 = $this->createMock(Milestone::class);
        $milestone1->method('getId')->willReturn($milestoneId1);
        $milestone1->method('getProject')->willReturn($project);

        $milestoneId2 = Uuid::v4();
        $milestone2 = $this->createMock(Milestone::class);
        $milestone2->method('getId')->willReturn($milestoneId2);
        $milestone2->method('getProject')->willReturn($project);

        // Mock controller
        $controller = $this->getMockBuilder(MilestoneController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller
            ->expects($this->exactly(2))
            ->method('render')
            ->willReturn(new Response());

        // Test both milestones can be accessed
        $response1 = $controller->index($project, $milestone1);
        $response2 = $controller->index($project, $milestone2);

        $this->assertInstanceOf(Response::class, $response1);
        $this->assertInstanceOf(Response::class, $response2);
    }

    /**
     * Test happy flow: verify the template receives correct data structure
     */
    public function testIndexPassesCorrectDataStructureToTemplate(): void
    {
        $project = $this->createMock(Project::class);
        $milestone = $this->createMock(Milestone::class);
        $milestone->method('getProject')->willReturn($project);

        $controller = $this->getMockBuilder(MilestoneController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('milestone/milestone.html.twig'),
                $this->callback(function ($params) {
                    // Verify the data structure passed to template
                    $this->assertIsArray($params);
                    $this->assertCount(2, $params);
                    $this->assertArrayHasKey('project', $params);
                    $this->assertArrayHasKey('milestone', $params);

                    // Verify types
                    $this->assertInstanceOf(Project::class, $params['project']);
                    $this->assertInstanceOf(Milestone::class, $params['milestone']);

                    return true;
                })
            )
            ->willReturn(new Response());

        $response = $controller->index($project, $milestone);

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test the validation: milestone belongs to project relationship
     */
    public function testMilestoneProjectRelationshipIsValidated(): void
    {
        // Create project
        $project = $this->createMock(Project::class);
        $project->method('getSlug')->willReturn('test-project');

        // Create milestone that belongs to this project
        $milestoneId = Uuid::v4();
        $milestone = $this->createMock(Milestone::class);
        $milestone->method('getId')->willReturn($milestoneId);
        $milestone->method('getProject')->willReturn($project);

        // Mock controller
        $controller = $this->getMockBuilder(MilestoneController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // The getProject method should be called to verify the relationship
        $milestone
            ->expects($this->atLeastOnce())
            ->method('getProject');

        $controller
            ->method('render')
            ->willReturn(new Response());

        $response = $controller->index($project, $milestone);

        $this->assertInstanceOf(Response::class, $response);
    }
}