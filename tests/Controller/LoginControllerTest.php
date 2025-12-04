<?php
// File: `tests/Controller/LoginControllerTest.php`
namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $testEmail;
    private string $testPassword = 'password123';

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // Email unique pour éviter les collisions avec la base existante
        $this->testEmail = 'test+' . uniqid('', true) . '@example.com';

        $user = new User();
        $user->setName('Jean');
        $user->setSecondName('Dupont');
        $user->setEmail($this->testEmail);
        $user->setPassword($passwordHasher->hashPassword($user, $this->testPassword));

        $em->persist($user);
        $em->flush();
    }

    public function testLoginCases(): void
    {
        $this->client->request('GET', '/en/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'doesNotExist@example.com',
            '_password' => 'whatever',
        ]);

        self::assertResponseRedirects('/en/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        $this->client->request('GET', '/en/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => $this->testEmail,
            '_password' => 'bad-password',
        ]);

        self::assertResponseRedirects('/en/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        $this->client->request('GET', '/en/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => $this->testEmail,
            '_password' => $this->testPassword,
        ]);

    }
}
