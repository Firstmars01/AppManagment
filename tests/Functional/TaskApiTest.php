<?php

namespace App\Tests\Functional;

use App\Entity\Milestone;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\TaskType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskApiTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testGetTasksCollection(): void
    {
        // Arrange
        $user = $this->createUser();
        $project = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user);
        $taskType = $this->createTaskType('Not started');
        $task = $this->createTask($milestone, $user, $taskType);
        $this->entityManager->flush();

        // Act
        $this->client->request('GET', '/api/tasks');

        // Assert
        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('hydra:member', $response);
        $this->assertGreaterThan(0, count($response['hydra:member']));

        // Verify task data
        $taskFound = false;
        foreach ($response['hydra:member'] as $item) {
            if ($item['label'] === $task->getLabel()) {
                $taskFound = true;
                $this->assertArrayHasKey('id', $item);
                $this->assertArrayHasKey('label', $item);
                $this->assertArrayHasKey('description', $item);
                $this->assertArrayHasKey('isFunctional', $item);
                $this->assertArrayHasKey('taskType', $item);
                break;
            }
        }

        $this->assertTrue($taskFound, 'Task not found in collection');
    }

    public function testGetTasksByMilestone(): void
    {
        // Arrange
        $user = $this->createUser();
        $project = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user);
        $taskType = $this->createTaskType('Not started');
        $task = $this->createTask($milestone, $user, $taskType);
        $this->entityManager->flush();

        // Act
        $this->client->request('GET', '/api/tasks?milestone=' . $milestone->getId());

        // Assert
        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('hydra:member', $response);
        $this->assertGreaterThan(0, count($response['hydra:member']));

        // Verify all tasks belong to the milestone
        foreach ($response['hydra:member'] as $item) {
            $this->assertEquals($milestone->getId()->toRfc4122(), $item['milestone']['id']);
        }
    }

    public function testGetTaskDetail(): void
    {
        // Arrange
        $user = $this->createUser();
        $project = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user);
        $taskType = $this->createTaskType('Not started');
        $task = $this->createTask($milestone, $user, $taskType);
        $this->entityManager->flush();

        // Act
        $this->client->request('GET', '/api/tasks/' . $task->getId());

        // Assert
        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($task->getLabel(), $response['label']);
        $this->assertEquals($task->getDescription(), $response['description']);
        $this->assertArrayHasKey('manager', $response);
        $this->assertArrayHasKey('milestone', $response);
        $this->assertArrayHasKey('requirements', $response);
        $this->assertArrayHasKey('taskType', $response);
        $this->assertArrayHasKey('daysEstimate', $response);
    }

    public function testStartTask(): void
    {
        // Arrange
        $user = $this->createUser();
        $project = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user);
        $notStartedType = $this->createTaskType('Not started');
        $startedType = $this->createTaskType('Started but not finished');
        $task = $this->createTask($milestone, $user, $notStartedType);
        $this->entityManager->flush();

        $taskId = $task->getId();

        // Act
        $this->client->request('PATCH', '/api/tasks/' . $taskId . '/start', [], [], [
            'CONTENT_TYPE' => 'application/merge-patch+json'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotNull($response['actualStartDate']);
        $this->assertEquals('Started but not finished', $response['taskType']['label']);
    }

    public function testFinishTask(): void
    {
        // Arrange
        $user = $this->createUser();
        $project = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user);
        $startedType = $this->createTaskType('Started but not finished');
        $finishedType = $this->createTaskType('Finished');
        $task = $this->createTask($milestone, $user, $startedType);
        $task->setActualStartDate(new \DateTimeImmutable('2024-01-01'));
        $this->entityManager->flush();

        $taskId = $task->getId();

        // Act
        $this->client->request('PATCH', '/api/tasks/' . $taskId . '/finish', [], [], [
            'CONTENT_TYPE' => 'application/merge-patch+json'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Finished', $response['taskType']['label']);
    }

    public function testGetNonExistentTask(): void
    {
        // Act
        $this->client->request('GET', '/api/tasks/00000000-0000-0000-0000-000000000000');

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setName('Bob');
        $user->setSecondName('Wilson');
        $user->setEmail('bob.wilson' . uniqid() . '@example.com');
        $user->setPassword('hashed_password');

        $this->entityManager->persist($user);

        return $user;
    }

    private function createProject(User $user): Project
    {
        $project = new Project();
        $project->setName('Test Project ' . uniqid());
        $project->setSlug('test-project-' . uniqid());
        $project->setOwner($user);

        $this->entityManager->persist($project);

        return $project;
    }

    private function createMilestone(Project $project, User $manager): Milestone
    {
        $milestone = new Milestone();
        $milestone->setLabel('Test Milestone ' . uniqid());
        $milestone->setProject($project);
        $milestone->setManager($manager);
        $milestone->setPlannedStartDate(new \DateTime('2024-01-01'));
        $milestone->setPlannedEndDate(new \DateTime('2024-03-31'));

        $this->entityManager->persist($milestone);

        return $milestone;
    }

    private function createTaskType(string $label): TaskType
    {
        // Try to find existing task type
        $existingType = $this->entityManager
            ->getRepository(TaskType::class)
            ->findOneBy(['label' => $label]);

        if ($existingType) {
            return $existingType;
        }

        $type = new TaskType();
        $type->setLabel($label);

        $this->entityManager->persist($type);

        return $type;
    }

    private function createTask(Milestone $milestone, User $manager, TaskType $type): Task
    {
        $task = new Task();
        $task->setLabel('Test Task ' . uniqid());
        $task->setDescription('Test task description');
        $task->setIsFunctional(true);
        $task->setMilestone($milestone);
        $task->setManager($manager);
        $task->setTaskType($type);
        $task->setDaysEstimate(5);
        $task->setPlannedStartDate(new \DateTimeImmutable('2024-01-15'));

        $this->entityManager->persist($task);

        return $task;
    }
}