<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrudRequirementControllerTest extends WebTestCase
{
    public function testIndexIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/crud/requirement');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testNewPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/crud/requirement/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }
}
