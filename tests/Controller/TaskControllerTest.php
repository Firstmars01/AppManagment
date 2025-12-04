<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testProjectNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/project/nonexistent-project/task/1');

        self::assertResponseStatusCodeSame(404);
    }

    public function testTaskRouteRequiresProjectAndId(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/project/test-project/task/9999');

        self::assertResponseStatusCodeSame(404);
    }
}
