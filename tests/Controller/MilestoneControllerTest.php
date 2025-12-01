<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MilestoneControllerTest extends WebTestCase
{
    public function testProjectNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/project/nonexistent-project/milestone/1');

        self::assertResponseStatusCodeSame(404);
    }

    public function testMilestoneRouteRequiresProjectAndId(): void
    {
        $client = static::createClient();
        $client->request('GET', '/project/test-project/milestone/9999');

        self::assertResponseStatusCodeSame(404);
    }
}
