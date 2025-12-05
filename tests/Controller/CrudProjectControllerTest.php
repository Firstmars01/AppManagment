<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrudProjectControllerTest extends WebTestCase
{
    private $client;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->user = $this->createUser();
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

    private function createProject(string $name): Project
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        // Add unique identifier to project name to avoid slug conflicts
        $uniqueName = $name . ' ' . uniqid();

        $project = new Project();
        $project->setName($uniqueName);
        $project->setOwner($this->user);
        $slugger = self::getContainer()->get('slugger');
        $project->computeSlug($slugger);

        $em->persist($project);
        $em->flush();

        return $project;
    }

    /**
     * Test Happy Flow: Index page displays successfully
     * Verifies that authenticated users can access the project list page
     */
    public function testIndexHappyFlow(): void
    {
        // Given: A logged-in user
        $this->client->loginUser($this->user);

        // When: User navigates to the project index page
        $this->client->request('GET', '/fr/crud/project');

        // Then: Page loads successfully with expected content
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    /**
     * Test Happy Flow: Index page displays existing projects
     * Verifies that created projects appear in the list
     */
    public function testIndexDisplaysProjectsHappyFlow(): void
    {
        // Given: A logged-in user and some projects
        $this->client->loginUser($this->user);
        $project1 = $this->createProject('E-commerce Platform');
        $project2 = $this->createProject('Mobile App');

        // When: User views the project index
        $this->client->request('GET', '/fr/crud/project');

        // Then: Both projects are displayed
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'E-commerce Platform');
        $this->assertSelectorTextContains('body', 'Mobile App');
    }

    /**
     * Test Happy Flow: Create a new project successfully
     * Verifies the complete creation workflow
     */
    public function testNewProjectHappyFlow(): void
    {
        // Given: A logged-in user on the new project form
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/fr/crud/project/new');

        // Then: Form is displayed correctly
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // When: User fills and submits the form
        $form = $crawler->filter('form')->form();
        $uniqueName = 'CRM System ' . uniqid();
        $form['project[name]'] = $uniqueName;
        $this->client->submit($form);

        // Then: User is redirected to index
        $this->assertResponseRedirects('/fr/crud/project');

        // And: The new project appears in the list
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'CRM System');
    }

    /**
     * Test Happy Flow: View a specific project
     * Verifies that project details are displayed correctly
     */
    public function testShowProjectHappyFlow(): void
    {
        // Given: A logged-in user and an existing project
        $this->client->loginUser($this->user);
        $project = $this->createProject('Portfolio Website');

        // When: User views the project details
        $this->client->request('GET', '/fr/crud/project/' . $project->getId());

        // Then: Project details are displayed
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Portfolio Website');
        $this->assertSelectorExists('a[href*="edit"]'); // Edit link present
        $this->assertSelectorExists('form[method="post"]'); // Delete form present
    }

    /**
     * Test Happy Flow: Edit an existing project successfully
     * Verifies the complete edit workflow
     */
    public function testEditProjectHappyFlow(): void
    {
        // Given: A logged-in user and an existing project
        $this->client->loginUser($this->user);
        $project = $this->createProject('Inventory System');

        // When: User navigates to edit form
        $crawler = $this->client->request('GET', '/fr/crud/project/' . $project->getId() . '/edit');

        // Then: Edit form is displayed with current values
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // When: User updates the project
        $form = $crawler->filter('form')->form();
        $form['project[name]'] = 'Advanced Inventory System';
        $this->client->submit($form);

        // Then: User is redirected to index
        $this->assertResponseRedirects('/fr/crud/project');

        // And: The updated project appears in the list
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Advanced Inventory System');
    }

    /**
     * Test Happy Flow: Delete a project successfully
     * Verifies the complete deletion workflow
     */
    public function testDeleteProjectHappyFlow(): void
    {
        // Given: A logged-in user and an existing project
        $this->client->loginUser($this->user);
        $project = $this->createProject('Legacy System');

        // When: User views the index page with the project
        $crawler = $this->client->request('GET', '/fr/crud/project');
        $this->assertSelectorTextContains('body', 'Legacy System');

        // And: User submits the delete form with valid CSRF token
        $form = $crawler->filter('form[action$="' . $project->getId() . '"]')->form();
        $this->client->submit($form);

        // Then: User is redirected to index
        $this->assertResponseRedirects('/fr/crud/project');

        // And: The deleted project no longer appears
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Legacy System');
    }

    /**
     * Test Happy Flow: Complete CRUD cycle
     * Verifies that a project can be created, read, updated, and deleted in sequence
     */
    public function testCompleteCrudCycleHappyFlow(): void
    {
        $this->client->loginUser($this->user);

        // CREATE: Create a new project
        $crawler = $this->client->request('GET', '/fr/crud/project/new');
        $form = $crawler->filter('form')->form();
        $uniqueName = 'Analytics Dashboard ' . uniqid();
        $form['project[name]'] = $uniqueName;
        $this->client->submit($form);

        $this->assertResponseRedirects('/fr/crud/project');
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Analytics Dashboard');

        // READ: View the project on index page
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Analytics Dashboard');

        // Get the project ID from the database to construct URLs
        $em = self::getContainer()->get('doctrine')->getManager();
        $project = $em->getRepository(Project::class)->findOneBy(['name' => $uniqueName]);
        $this->assertNotNull($project);

        // UPDATE: Edit the project
        $crawler = $this->client->request('GET', '/fr/crud/project/' . $project->getId() . '/edit');
        $form = $crawler->filter('form')->form();
        $updatedName = 'Advanced Analytics Dashboard ' . uniqid();
        $form['project[name]'] = $updatedName;
        $this->client->submit($form);

        $this->assertResponseRedirects('/fr/crud/project');
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Advanced Analytics Dashboard');

        // DELETE: Remove the project
        $deleteForm = $crawler->filter('form[action$="' . $project->getId() . '"]')->form();
        $this->client->submit($deleteForm);

        $this->assertResponseRedirects('/fr/crud/project');
        $this->client->followRedirect();
        $this->assertSelectorTextNotContains('body', 'Advanced Analytics Dashboard');
    }

    /**
     * Test Happy Flow: Empty state on index page
     * Verifies that the index page handles empty state gracefully
     */
    public function testIndexEmptyStateHappyFlow(): void
    {
        // Given: A logged-in user with no projects
        $this->client->loginUser($this->user);

        // When: User views the project index
        $this->client->request('GET', '/fr/crud/project');

        // Then: Page loads successfully
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');

        // And: New project link is available
        $this->assertSelectorExists('a[href*="/new"]');
    }

    /**
     * Test Happy Flow: Access control - authenticated users only
     * Verifies that only authenticated users can access project pages
     */
    public function testAuthenticationRequiredHappyFlow(): void
    {
        // Given: An anonymous user (not logged in)

        // When: User tries to access the project index
        $this->client->request('GET', '/fr/crud/project');

        // Then: User is redirected to login page
        $this->assertResponseRedirects();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }
}