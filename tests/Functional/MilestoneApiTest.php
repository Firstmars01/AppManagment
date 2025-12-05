<?php

namespace App\Tests\Functional;

use App\Entity\Milestone;
use App\Entity\Project;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Tools\SchemaTool;

class MilestoneApiTest extends WebTestCase
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

    /** ------------------------------
     *    TESTS GET COLLECTION
     * ------------------------------ */
    public function testGetMilestonesCollection(): void
    {
        $user     = $this->createUser();
        $project  = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user, 'MS1');
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request('GET', '/api/milestones');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('member', $response);
        $this->assertGreaterThan(0, count($response['member']));

        $found = false;
        foreach ($response['member'] as $item) {
            if ($item['label'] === 'MS1') {
                $found = true;
                $this->assertArrayHasKey('id', $item);
                $this->assertArrayHasKey('label', $item);
                $this->assertArrayHasKey('project', $item);
                break;
            }
        }
        $this->assertTrue($found, "Milestone not found in collection response");
    }

    /** ------------------------------
     *    TEST GET DETAIL
     * ------------------------------ */
    public function testGetMilestoneDetail(): void
    {
        $user     = $this->createUser();
        $project  = $this->createProject($user);
        $milestone = $this->createMilestone($project, $user, 'MS1');

        $this->entityManager->flush();
        $id = $milestone->getId();
        $this->entityManager->clear();

        $this->client->request("GET", "/api/milestones/$id");

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('MS1', $response['label']);
        $this->assertArrayHasKey('manager', $response);
        $this->assertArrayHasKey('tasks', $response);
        $this->assertArrayHasKey('progress', $response);
        $this->assertArrayHasKey('delayInDays', $response);
    }

    /** ------------------------------
     *   TEST 404 NOT FOUND
     * ------------------------------ */
    public function testGetNonExistentMilestone(): void
    {
        $this->client->request(
            'GET',
            '/api/milestones/00000000-0000-0000-0000-000000000000'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /** ------------------------------
     *   TEST FILTRE PAR PROJECT
     * ------------------------------ */
    public function testFilterMilestonesByProject(): void
    {
        $user = $this->createUser();

        $projectA = $this->createProject($user);
        $projectB = $this->createProject($user);

        $msA = $this->createMilestone($projectA, $user, 'MS-A');
        $msB = $this->createMilestone($projectB, $user, 'MS-B');

        $this->entityManager->flush();
        $this->entityManager->clear();

        // Filter by project A
        $this->client->request('GET', '/api/milestones?project=' . $projectA->getId());
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $response['member']);
        $this->assertEquals('MS-A', $response['member'][0]['label']);
    }

    /** ------------------------------
     *   HELPERS
     * ------------------------------ */
    private function createUser(): User
    {
        $u = new User();
        $u->setName("John");
        $u->setSecondName("Doe");
        $u->setEmail('john'.uniqid().'@example.com');
        $u->setPassword("hashed");
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

    private function createMilestone(Project $p, User $manager, string $label): Milestone
    {
        $m = new Milestone();
        $m->setProject($p);
        $m->setManager($manager);
        $m->setLabel($label);
        $this->entityManager->persist($m);
        return $m;
    }
}
