<?php

namespace App\Tests\Functional;

use App\Entity\Requirement;
use App\Entity\RequirementType;
use App\Entity\Project;
use App\Entity\User;
use App\Entity\Task;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RequirementApiTest extends WebTestCase
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
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

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
    public function testGetRequirementsCollection(): void
    {
        $user = $this->createUser();
        $project = $this->createProject($user);
        $requirement = $this->createRequirement($project, true, "Requirement A");

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request("GET", "/api/requirements");
        $this->assertResponseIsSuccessful();

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey("member", $res);

        $found = false;
        foreach ($res["member"] as $item) {
            if ($item["description"] === "Requirement A") {
                $found = true;
                $this->assertArrayHasKey("id", $item);
                $this->assertArrayHasKey("project", $item);
                $this->assertArrayHasKey("isFunctional", $item);
                break;
            }
        }

        $this->assertTrue($found, "Requirement not found in collection");
    }

    /* ============================================================
     *   GET DETAIL
     * ============================================================ */
    public function testGetRequirementDetail(): void
    {
        $user = $this->createUser();
        $project = $this->createProject($user);
        $requirement = $this->createRequirement($project, true, "Requirement Detail");

        $this->entityManager->flush();
        $id = $requirement->getId();
        $this->entityManager->clear();

        $this->client->request("GET", "/api/requirements/$id");
        $this->assertResponseIsSuccessful();

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("Requirement Detail", $res["description"]);
        $this->assertArrayHasKey("isFunctional", $res);
        $this->assertArrayHasKey("requirementType", $res);
        $this->assertArrayHasKey("project", $res);
    }

    /* ============================================================
     *   GET NOT FOUND
     * ============================================================ */
    public function testGetNonExistentRequirement(): void
    {
        $this->client->request(
            "GET",
            "/api/requirements/00000000-0000-0000-0000-000000000000"
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
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

    private function createRequirement(Project $project, bool $isFunctional, string $description): Requirement
    {
        $type = new RequirementType();
        $type->setLabel("Default Type");
        $this->entityManager->persist($type);

        $r = new Requirement();
        $r->setProject($project);
        $r->setIsFunctional($isFunctional);
        $r->setDescription($description);
        $r->setRequirementType($type);

        $this->entityManager->persist($r);
        return $r;
    }
}
