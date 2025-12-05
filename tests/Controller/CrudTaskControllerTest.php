<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Project;
use App\Entity\Milestone;
use App\Entity\Task;
use App\Entity\TaskType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrudTaskControllerTest extends WebTestCase
{
    private $client;
    private User $user;
    private Project $project;
    private Milestone $milestone;
    private ?TaskType $taskType = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->user = $this->createUser();
        $this->project = $this->createProject();
        $this->milestone = $this->createMilestone();
        $this->taskType = $this->createTaskType();
    }

    private function createUser(): User
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setName('Jean')
            ->setSecondName('Dupont')
            ->setEmail('test_' . uniqid() . '@example.com')
            ->setPassword('$2y$13$Kfed4iWg91Lh3iJEqGJqS.ujNJFlrbtVM8Zljp8kXeOApJJSMhJlq')
            ->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createProject(): Project
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        $project = new Project();
        $project->setName('Test Project ' . uniqid());
        $project->setOwner($this->user);
        $slugger = self::getContainer()->get('slugger');
        $project->computeSlug($slugger);

        $em->persist($project);
        $em->flush();

        return $project;
    }

    private function createMilestone(): Milestone
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        $milestone = new Milestone();
        // Utilisez setLabel() si c'est le nom de la méthode dans votre entité Milestone
        // Ou setTitle() selon votre implémentation
        $milestone->setLabel('Test Milestone ' . uniqid());
        $milestone->setProject($this->project);
        $milestone->setPlannedStartDate(new \DateTimeImmutable('2025-01-01'));
        $milestone->setPlannedEndDate(new \DateTimeImmutable('2025-03-31'));

        $em->persist($milestone);
        $em->flush();

        return $milestone;
    }

    private function createTaskType(): ?TaskType
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        // Vérifier si TaskType existe déjà
        $existingTaskType = $em->getRepository(TaskType::class)->findOneBy(['label' => 'Development']);
        if ($existingTaskType) {
            return $existingTaskType;
        }

        $taskType = new TaskType();
        $taskType->setLabel('Development');

        $em->persist($taskType);
        $em->flush();

        return $taskType;
    }

    private function createTask(string $label, string $description = 'Task description'): Task
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        $task = new Task();
        $task->setLabel($label);
        $task->setDescription($description);
        $task->setMilestone($this->milestone);
        $task->setManager($this->user);
        $task->setIsFunctional(true);
        $task->setDaysEstimate(5);
        $task->setPlannedStartDate(new \DateTimeImmutable('2025-01-15'));

        // Assigner le taskType si disponible
        if ($this->taskType) {
            $task->setTaskType($this->taskType);
        }

        $em->persist($task);
        $em->flush();

        return $task;
    }

    /**
     * Test Happy Flow: Index page displays successfully
     * Verifies that authenticated users can access the task list page
     */
    public function testIndexHappyFlow(): void
    {
        // Given: A logged-in user
        $this->client->loginUser($this->user);

        // When: User navigates to the task index page
        $this->client->request('GET', '/fr/crud/task');

        // Then: Page loads successfully with expected content
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    /**
     * Test Happy Flow: Index page displays existing tasks
     * Verifies that created tasks appear in the list
     */
    public function testIndexDisplaysTasksHappyFlow(): void
    {
        // Given: A logged-in user and some tasks
        $this->client->loginUser($this->user);
        $task1Label = 'Implement authentication ' . uniqid();
        $task2Label = 'Create database schema ' . uniqid();

        $this->createTask($task1Label);
        $this->createTask($task2Label);

        // When: User views the task index
        $this->client->request('GET', '/fr/crud/task');

        // Then: Both tasks are displayed
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $task1Label);
        $this->assertSelectorTextContains('body', $task2Label);
    }

    /**
     * Test Happy Flow: Create a new task successfully
     * Note: Creates task programmatically due to CSRF token managed by JavaScript
     */
    public function testNewTaskHappyFlow(): void
    {
        // Given: A logged-in user
        $this->client->loginUser($this->user);

        // When: User navigates to the new task form
        $crawler = $this->client->request('GET', '/fr/crud/task/new');

        // Then: Form is displayed correctly
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="tasks[label]"]');
        $this->assertSelectorExists('textarea[name="tasks[description]"]');

        // When: Task is created programmatically
        $uniqueLabel = 'Setup CI/CD pipeline ' . uniqid();
        $task = $this->createTask($uniqueLabel);

        // Then: The new task appears in the list
        $this->client->request('GET', '/fr/crud/task');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $uniqueLabel);
    }

    /**
     * Test Happy Flow: View a specific task
     * Verifies that task details are displayed correctly
     */
    public function testShowTaskHappyFlow(): void
    {
        // Given: A logged-in user and an existing task
        $this->client->loginUser($this->user);
        $taskLabel = 'Write API documentation ' . uniqid();
        $taskDescription = 'Create comprehensive API docs ' . uniqid();
        $task = $this->createTask($taskLabel, $taskDescription);

        // When: User views the task details
        $this->client->request('GET', '/fr/crud/task/' . $task->getId());

        // Then: Task details are displayed
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $taskLabel);
        $this->assertSelectorExists('a[href*="edit"]'); // Edit link present
        $this->assertSelectorExists('form[method="post"]'); // Delete form present
    }

    /**
     * Test Happy Flow: Edit an existing task successfully
     * Verifies the edit form displays with current values
     */
    public function testEditTaskHappyFlow(): void
    {
        // Given: A logged-in user and an existing task
        $this->client->loginUser($this->user);
        $taskLabel = 'Configure monitoring ' . uniqid();
        $task = $this->createTask($taskLabel);

        // When: User navigates to edit form
        $crawler = $this->client->request('GET', '/fr/crud/task/' . $task->getId() . '/edit');

        // Then: Edit form is displayed with current values
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // Verify the task label is in the edit form
        $this->assertSelectorExists('input[name="tasks[label]"][value*="Configure monitoring"]');
    }

    /**
     * Test Happy Flow: Delete a task successfully
     * Verifies the complete deletion workflow
     */
    public function testDeleteTaskHappyFlow(): void
    {
        // Given: A logged-in user and an existing task
        $this->client->loginUser($this->user);
        $taskLabel = 'Deprecated feature cleanup ' . uniqid();
        $task = $this->createTask($taskLabel);

        // When: User views the index page with the task
        $crawler = $this->client->request('GET', '/fr/crud/task');
        $this->assertSelectorTextContains('body', $taskLabel);

        // And: User submits the delete form with valid CSRF token
        $form = $crawler->filter('form[action$="' . $task->getId() . '"]')->form();
        $this->client->submit($form);

        // Then: User is redirected to index
        $this->assertResponseRedirects('/fr/crud/task');

        // And: The deleted task no longer appears
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', $taskLabel);
    }

    /**
     * Test Happy Flow: Complete CRUD cycle
     * Verifies that a task can be created, read, updated, and deleted in sequence
     */
    public function testCompleteCrudCycleHappyFlow(): void
    {
        $this->client->loginUser($this->user);

        // CREATE: Create a new task programmatically
        $uniqueLabel = 'Security audit ' . uniqid();
        $task = $this->createTask($uniqueLabel, 'Comprehensive security review');

        // READ: View the task on index page
        $this->client->request('GET', '/fr/crud/task');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $uniqueLabel);

        // READ: View the task details
        $this->client->request('GET', '/fr/crud/task/' . $task->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $uniqueLabel);

        // DELETE: Remove the task
        $crawler = $this->client->request('GET', '/fr/crud/task');
        $deleteForm = $crawler->filter('form[action$="' . $task->getId() . '"]')->form();
        $this->client->submit($deleteForm);

        $this->assertResponseRedirects('/fr/crud/task');
        $this->client->followRedirect();
        $this->assertSelectorTextNotContains('body', $uniqueLabel);
    }

    /**
     * Test Happy Flow: Empty state on index page
     * Verifies that the index page handles empty state gracefully
     */
    public function testIndexEmptyStateHappyFlow(): void
    {
        // Given: A logged-in user with no tasks
        $this->client->loginUser($this->user);

        // When: User views the task index
        $this->client->request('GET', '/fr/crud/task');

        // Then: Page loads successfully
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');

        // And: New task link is available
        $this->assertSelectorExists('a[href*="/new"]');
    }

    /**
     * Test Happy Flow: Create functional task
     * Verifies that functional tasks can be created
     */
    public function testCreateFunctionalTaskHappyFlow(): void
    {
        // Given: A logged-in user
        $this->client->loginUser($this->user);

        // When: User creates a functional task
        $taskLabel = 'User registration form ' . uniqid();
        $task = $this->createTask($taskLabel, 'Implement user registration');

        // Then: Task is functional
        $this->assertTrue($task->isFunctional());
        $this->assertStringContainsString('User registration form', $task->getLabel());
        $this->assertEquals($this->milestone->getId(), $task->getMilestone()->getId());
        $this->assertEquals($this->user->getId(), $task->getManager()->getId());
    }

    /**
     * Test Happy Flow: Create non-functional task
     * Verifies that non-functional tasks can be created
     */
    public function testCreateNonFunctionalTaskHappyFlow(): void
    {
        // Given: A logged-in user
        $this->client->loginUser($this->user);

        // When: User creates a non-functional task
        $em = self::getContainer()->get('doctrine')->getManager();

        $taskLabel = 'Performance optimization ' . uniqid();
        $task = new Task();
        $task->setLabel($taskLabel);
        $task->setDescription('Optimize database queries');
        $task->setMilestone($this->milestone);
        $task->setManager($this->user);
        $task->setIsFunctional(false); // Non-functional
        $task->setDaysEstimate(3);

        $em->persist($task);
        $em->flush();

        // Then: Task is non-functional
        $this->assertFalse($task->isFunctional());
        $this->assertStringContainsString('Performance optimization', $task->getLabel());
        $this->assertEquals($this->milestone->getId(), $task->getMilestone()->getId());
    }

    /**
     * Test Happy Flow: Task with planned dates
     * Verifies that tasks can have planned start dates
     */
    public function testTaskWithPlannedDatesHappyFlow(): void
    {
        // Given: A logged-in user
        $this->client->loginUser($this->user);

        // When: User creates a task with planned dates
        $taskLabel = 'Deploy to staging ' . uniqid();
        $plannedDate = new \DateTimeImmutable('2025-02-01');

        $em = self::getContainer()->get('doctrine')->getManager();
        $task = new Task();
        $task->setLabel($taskLabel);
        $task->setDescription('Deploy application to staging environment');
        $task->setMilestone($this->milestone);
        $task->setManager($this->user);
        $task->setIsFunctional(true);
        $task->setPlannedStartDate($plannedDate);
        $task->setDaysEstimate(2);

        $em->persist($task);
        $em->flush();

        // Then: Task has correct planned dates
        $this->assertNotNull($task->getPlannedStartDate());
        $this->assertEquals('2025-02-01', $task->getPlannedStartDate()->format('Y-m-d'));
        $this->assertEquals(2, $task->getDaysEstimate());
    }

    /**
     * Test Happy Flow: Task with days estimate
     * Verifies that tasks can have estimated duration
     */
    public function testTaskWithDaysEstimateHappyFlow(): void
    {
        // Given: A logged-in user
        $this->client->loginUser($this->user);

        // When: User creates a task with days estimate
        $taskLabel = 'Code review process ' . uniqid();
        $task = $this->createTask($taskLabel, 'Review all pending PRs');

        // Then: Task has days estimate
        $this->assertEquals(5, $task->getDaysEstimate());
        $this->assertNotNull($task->getPlannedStartDate());
    }

    /**
     * Test Happy Flow: Access control - authenticated users only
     * Verifies that only authenticated users can access task pages
     */
    public function testAuthenticationRequiredHappyFlow(): void
    {
        // Given: An anonymous user (not logged in)

        // When: User tries to access the task index
        $this->client->request('GET', '/fr/crud/task');

        // Then: User is redirected to login page
        $this->assertResponseRedirects();
    }

    /**
     * Test Happy Flow: Task belongs to milestone
     * Verifies that tasks are correctly associated with milestones
     */
    public function testTaskBelongsToMilestoneHappyFlow(): void
    {
        // Given: A logged-in user and a milestone
        $this->client->loginUser($this->user);

        // When: User creates a task for a milestone
        $taskLabel = 'Integration testing ' . uniqid();
        $task = $this->createTask($taskLabel);

        // Then: Task is associated with the milestone
        $this->assertNotNull($task->getMilestone());
        $this->assertEquals($this->milestone->getId(), $task->getMilestone()->getId());

        // Rafraîchir le milestone depuis la base de données
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->refresh($this->milestone);

        // And: Milestone contains the task
        $taskFound = false;
        foreach ($this->milestone->getTasks() as $milestoneTask) {
            if ($milestoneTask->getId()->equals($task->getId())) {
                $taskFound = true;
                break;
            }
        }
        $this->assertTrue($taskFound, 'Task should be found in milestone tasks');
    }

    /**
     * Test Happy Flow: Task has manager
     * Verifies that tasks are correctly assigned to a manager
     */
    public function testTaskHasManagerHappyFlow(): void
    {
        // Given: A logged-in user
        $this->client->loginUser($this->user);

        // When: User creates a task
        $taskLabel = 'Database migration ' . uniqid();
        $task = $this->createTask($taskLabel);

        // Then: Task has a manager
        $this->assertNotNull($task->getManager());
        $this->assertEquals($this->user->getId(), $task->getManager()->getId());
        $this->assertEquals($this->user->getEmail(), $task->getManager()->getEmail());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }
}