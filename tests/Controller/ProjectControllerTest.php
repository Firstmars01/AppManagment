<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = ProjectControllerTest::createClient();
        $client->request('GET', '/project');

        self::assertResponseIsSuccessful();
    }
}
