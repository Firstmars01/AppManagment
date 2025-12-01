<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
    }

    public function testShow(): void
    {
        $client = static::createClient();
        $slug = 'projet-alpha';
        $client->request('GET', "/project/$slug");

        self::assertResponseIsSuccessful();
    }
}
