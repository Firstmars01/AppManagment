<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DashboardControllerTest extends WebTestCase
{
    public function testIndexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        $status = $client->getResponse()->getStatusCode();
        self::assertTrue(in_array($status, [302, 403]), sprintf('Status 302 or 403, receive %d', $status));
    }

    public function testIndexWhenAuthenticated(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'jean.dupont@example.com']);

        self::assertNotNull($testUser, 'No user with jean.dupont@example.com');

        $client->loginUser($testUser);
        $client->request('GET', '/dashboard');

        self::assertResponseIsSuccessful();
    }
}
