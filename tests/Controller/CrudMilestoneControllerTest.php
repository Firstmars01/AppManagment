<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrudMilestoneControllerTest extends WebTestCase
{
    public function testIndexIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/crud/milestone');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testNewPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/crud/milestone/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }
}
