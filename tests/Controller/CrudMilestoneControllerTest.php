<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Milestone;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrudMilestoneControllerTest extends WebTestCase
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

    private function createMilestone(string $label): Milestone
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        $milestone = new Milestone();
        $milestone->setLabel($label);

        $em->persist($milestone);
        $em->flush();

        return $milestone;
    }

    /**
     * Test Happy Flow: Index page displays successfully
     * Verifies that authenticated users can access the milestone list page
     */
    public function testIndexHappyFlow(): void
    {
        // Given: A logged-in user
        $this->client->loginUser($this->user);

        // When: User navigates to the milestone index page
        $this->client->request('GET', '/fr/crud/milestone');

        // Then: Page loads successfully with expected content
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    /**
     * Test Happy Flow: Index page displays existing milestones
     * Verifies that created milestones appear in the list
     */
    public function testIndexDisplaysMilestonesHappyFlow(): void
    {
        // Given: A logged-in user and some milestones
        $this->client->loginUser($this->user);
        $milestone1 = $this->createMilestone('Q1 Release');
        $milestone2 = $this->createMilestone('Q2 Release');

        // When: User views the milestone index
        $this->client->request('GET', '/fr/crud/milestone');

        // Then: Both milestones are displayed
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Q1 Release');
        $this->assertSelectorTextContains('body', 'Q2 Release');
    }

    /**
     * Test Happy Flow: Create a new milestone successfully
     * Verifies the complete creation workflow
     */
    public function testNewMilestoneHappyFlow(): void
    {
        // Given: A logged-in user on the new milestone form
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/fr/crud/milestone/new');

        // Then: Form is displayed correctly
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // When: User fills and submits the form
        $form = $crawler->filter('form')->form();
        $form['milestone[label]'] = 'Version 2.0 Launch';
        $this->client->submit($form);

        // Then: User is redirected to index
        $this->assertResponseRedirects('/fr/crud/milestone');

        // And: The new milestone appears in the list
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Version 2.0 Launch');
    }

    /**
     * Test Happy Flow: View a specific milestone
     * Verifies that milestone details are displayed correctly
     */
    public function testShowMilestoneHappyFlow(): void
    {
        // Given: A logged-in user and an existing milestone
        $this->client->loginUser($this->user);
        $milestone = $this->createMilestone('Beta Release');

        // When: User views the milestone details
        $this->client->request('GET', '/fr/crud/milestone/' . $milestone->getId());

        // Then: Milestone details are displayed
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Beta Release');
        $this->assertSelectorExists('a[href*="edit"]'); // Edit link present
        $this->assertSelectorExists('form[method="post"]'); // Delete form present
    }

    /**
     * Test Happy Flow: Edit an existing milestone successfully
     * Verifies the complete edit workflow
     */
    public function testEditMilestoneHappyFlow(): void
    {
        // Given: A logged-in user and an existing milestone
        $this->client->loginUser($this->user);
        $milestone = $this->createMilestone('Alpha Release');

        // When: User navigates to edit form
        $crawler = $this->client->request('GET', '/fr/crud/milestone/' . $milestone->getId() . '/edit');

        // Then: Edit form is displayed with current values
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // When: User updates the milestone
        $form = $crawler->filter('form')->form();
        $form['milestone[label]'] = 'Alpha Release v1.1';
        $this->client->submit($form);

        // Then: User is redirected to index
        $this->assertResponseRedirects('/fr/crud/milestone');

        // And: The updated milestone appears in the list
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Alpha Release v1.1');
    }

    /**
     * Test Happy Flow: Delete a milestone successfully
     * Verifies the complete deletion workflow
     */
    public function testDeleteMilestoneHappyFlow(): void
    {
        // Given: A logged-in user and an existing milestone
        $this->client->loginUser($this->user);
        $milestone = $this->createMilestone('Old Release');

        // When: User views the index page with the milestone
        $crawler = $this->client->request('GET', '/fr/crud/milestone');
        $this->assertSelectorTextContains('body', 'Old Release');

        // And: User submits the delete form with valid CSRF token
        $form = $crawler->filter('form[action$="' . $milestone->getId() . '"]')->form();
        $this->client->submit($form);

        // Then: User is redirected to index
        $this->assertResponseRedirects('/fr/crud/milestone');

        // And: The deleted milestone no longer appears
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Old Release');
    }

    /**
     * Test Happy Flow: Complete CRUD cycle
     * Verifies that a milestone can be created, read, updated, and deleted in sequence
     */
    public function testCompleteCrudCycleHappyFlow(): void
    {
        $this->client->loginUser($this->user);

        // CREATE: Create a new milestone
        $crawler = $this->client->request('GET', '/fr/crud/milestone/new');
        $form = $crawler->filter('form')->form();
        $form['milestone[label]'] = 'Feature Complete';
        $this->client->submit($form);

        $this->assertResponseRedirects('/fr/crud/milestone');
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Feature Complete');

        // READ: View the milestone on index page
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Feature Complete');

        // Get the milestone ID from the database to construct URLs
        $em = self::getContainer()->get('doctrine')->getManager();
        $milestone = $em->getRepository(Milestone::class)->findOneBy(['label' => 'Feature Complete']);
        $this->assertNotNull($milestone);

        // UPDATE: Edit the milestone
        $crawler = $this->client->request('GET', '/fr/crud/milestone/' . $milestone->getId() . '/edit');
        $form = $crawler->filter('form')->form();
        $form['milestone[label]'] = 'Feature Complete v2';
        $this->client->submit($form);

        $this->assertResponseRedirects('/fr/crud/milestone');
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Feature Complete v2');

        // DELETE: Remove the milestone
        $deleteForm = $crawler->filter('form[action$="' . $milestone->getId() . '"]')->form();
        $this->client->submit($deleteForm);

        $this->assertResponseRedirects('/fr/crud/milestone');
        $this->client->followRedirect();
        $this->assertSelectorTextNotContains('body', 'Feature Complete v2');
    }

    /**
     * Test Happy Flow: Empty state on index page
     * Verifies that the index page handles empty state gracefully
     */
    public function testIndexEmptyStateHappyFlow(): void
    {
        // Given: A logged-in user with no milestones
        $this->client->loginUser($this->user);

        // When: User views the milestone index
        $this->client->request('GET', '/fr/crud/milestone');

        // Then: Page loads successfully
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');

        // And: New milestone link is available
        $this->assertSelectorExists('a[href*="/new"]');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }
}