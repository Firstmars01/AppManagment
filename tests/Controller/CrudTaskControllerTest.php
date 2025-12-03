<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrudTaskControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/crud/task');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testNewPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/crud/task/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }
}
