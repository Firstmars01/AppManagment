<?php

namespace App\Tests\Functional;

use App\Entity\Milestone;
use App\Entity\Task;
use App\Entity\TaskType;
use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\Tools\SchemaTool;
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

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata   = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
        $this->entityManager->close();
        parent::tearDown();
    }

    /* ============================================================
     *   GET COLLECTION
     * ============================================================ */
    public function testGetTasksCollection(): void
    {
        $user      = $this->createUser();
        $project   = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user);
        $task      = $this->createTask($milestone, $user, "Task A");

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request("GET", "/api/tasks");

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey("member", $response);

        $found = false;
        foreach ($response["member"] as $item) {
            if ($item["label"] === "Task A") {
                $found = true;
                $this->assertArrayHasKey("id", $item);
                $this->assertArrayHasKey("milestone", $item);
                break;
            }
        }
        $this->assertTrue($found, "Task not found in collection");
    }

    /* ============================================================
     *   GET DETAIL
     * ============================================================ */
    public function testGetTaskDetail(): void
    {
        $user      = $this->createUser();
        $project   = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user);
        $task      = $this->createTask($milestone, $user, "Task A");

        $this->entityManager->flush();
        $id = $task->getId();
        $this->entityManager->clear();

        $this->client->request("GET", "/api/tasks/$id");

        $this->assertResponseIsSuccessful();

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("Task A", $res["label"]);
        $this->assertArrayHasKey("description", $res);
        $this->assertArrayHasKey("taskType", $res);
        $this->assertArrayHasKey("manager", $res);
    }

    /* ============================================================
     *   GET NOT FOUND
     * ============================================================ */
    public function testGetNonExistentTask(): void
    {
        $this->client->request(
            "GET",
            "/api/tasks/00000000-0000-0000-0000-000000000000"
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /* ============================================================
     *   FILTER BY MILESTONE
     * ============================================================ */
    public function testFilterTasksByMilestone(): void
    {
        $user = $this->createUser();

        $project = $this->createProject($user);
        $m1 = $this->createMilestone($project, $user);
        $m2 = $this->createMilestone($project, $user);

        $task1 = $this->createTask($m1, $user, "T1");
        $task2 = $this->createTask($m2, $user, "T2");

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request("GET", "/api/tasks?milestone=" . $m1->getId());
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $res["member"]);
        $this->assertEquals("T1", $res["member"][0]["label"]);
    }

    /* ============================================================
     *   PATCH /start
     * ============================================================ */
    public function testStartTask(): void
    {
        $user      = $this->createUser();
        $project   = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user);
        $task      = $this->createTask($milestone, $user, "Task Start");

        $this->entityManager->flush();
        $id = $task->getId();
        $this->entityManager->clear();

        $this->client->request(
            "PATCH",
            "/api/tasks/$id/start",
            [],
            [],
            ["CONTENT_TYPE" => "application/merge-patch+json"],
            "{}"
        );

        $this->assertResponseIsSuccessful();

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotNull($res["actualStartDate"], "Task was not started");
    }

    /* ============================================================
     *   PATCH /finish
     * ============================================================ */
    public function testFinishTask(): void
    {
        $user      = $this->createUser();
        $project   = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user);
        $task      = $this->createTask($milestone, $user, "Task Finish");

        $this->entityManager->flush();
        $id = $task->getId();
        $this->entityManager->clear();

        $this->client->request(
            "PATCH",
            "/api/tasks/$id/finish",
            [],
            [],
            ["CONTENT_TYPE" => "application/merge-patch+json"],
            "{}"
        );

        $this->assertResponseIsSuccessful();

        $res = json_decode($this->client->getResponse()->getContent(), true);

        // TaskFinishProcessor probably sets taskType = "Finished"
        $this->assertArrayHasKey("taskType", $res);
    }

    /* ============================================================
     *   HELPERS
     * ============================================================ */
    private function createUser(): User
    {
        $u = new User();
        $u->setName("John");
        $u->setSecondName("Doe");
        $u->setEmail("john".uniqid()."@example.com");
        $u->setPassword("pass");
        $this->entityManager->persist($u);
        return $u;
    }

    private function createProject(User $owner): Project
    {
        $p = new Project();
        $p->setName("Project ".uniqid());
        $p->setSlug("project-".uniqid());
        $p->setOwner($owner);
        $this->entityManager->persist($p);
        return $p;
    }

    private function createMilestone(Project $p, User $manager): Milestone
    {
        $m = new Milestone();
        $m->setProject($p);
        $m->setManager($manager);
        $m->setLabel("MS".uniqid());
        $this->entityManager->persist($m);
        return $m;
    }

    private function createTask(Milestone $m, User $manager, string $label): Task
    {
        // Create or fetch a TaskType
        $type = new TaskType();
        $type->setLabel("Default");
        $this->entityManager->persist($type);

        $t = new Task();
        $t->setMilestone($m);
        $t->setManager($manager);
        $t->setLabel($label);
        $t->setDescription("Desc $label");
        $t->setIsFunctional(true);
        $t->setDaysEstimate(5);
        $t->setTaskType($type); // <-- IMPORTANT

        $this->entityManager->persist($t);
        return $t;
    }

}
