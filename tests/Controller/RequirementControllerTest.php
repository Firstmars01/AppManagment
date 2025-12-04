<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RequirementControllerTest extends WebTestCase
{
    public function testProjectNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/project/nonexistent-project/requirement/1');

        self::assertResponseStatusCodeSame(404);
    }

    public function testRequirementRouteRequiresProjectAndId(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/project/test-project/requirement/9999');

        self::assertResponseStatusCodeSame(404);
    }
}
