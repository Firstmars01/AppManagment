<?php

namespace App\Tests\Functional;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Tools\SchemaTool;

class ProjectApiTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        // Create database schema for tests
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        // Start transaction
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback transaction to clean up test data
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        $this->entityManager->close();
        parent::tearDown();
    }

    public function testGetProjectsCollection(): void
    {
        // Arrange: Create a user and a project
        $user = $this->createUser();
        $project = $this->createProject($user);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Act: Get the collection
        $this->client->request('GET', '/api/projects');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('member', $response);
        $this->assertGreaterThan(0, count($response['member']));

        // Check that the response contains our project
        $projectFound = false;
        foreach ($response['member'] as $item) {
            if ($item['name'] === $project->getName()) {
                $projectFound = true;
                $this->assertArrayHasKey('id', $item);
                $this->assertArrayHasKey('name', $item);
                $this->assertArrayHasKey('slug', $item);
                $this->assertArrayHasKey('progress', $item);
                $this->assertArrayHasKey('requirementsCoverage', $item);
                break;
            }
        }

        $this->assertTrue($projectFound, 'Project not found in collection');
    }

    public function testGetProjectDetail(): void
    {
        // Arrange
        $user = $this->createUser();
        $project = $this->createProject($user);
        $this->entityManager->flush();
        $projectId = $project->getId();
        $this->entityManager->clear();

        // Act
        $this->client->request('GET', '/api/projects/' . $projectId);

        // Assert
        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($project->getName(), $response['name']);
        $this->assertEquals($project->getSlug(), $response['slug']);
        $this->assertArrayHasKey('owner', $response);
        $this->assertArrayHasKey('milestones', $response);
        $this->assertArrayHasKey('requirements', $response);
        $this->assertArrayHasKey('progress', $response);
        $this->assertArrayHasKey('requirementsCoverage', $response);
    }

    public function testGetNonExistentProject(): void
    {
        // Act
        $this->client->request('GET', '/api/projects/00000000-0000-0000-0000-000000000000');

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setName('John');
        $user->setSecondName('Doe');
        $user->setEmail('john.doe' . uniqid() . '@example.com');
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
}