<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use Psr\Cache\CacheItemPoolInterface;
use Shelf\Book;
use Shelf\BookRepository;
use Shelf\Isbn;
use Shelf\Member;
use Shelf\MemberRepository;
use Shelf\Role;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * What every test of the pages and the API needs (chapter 77): a browser that sends requests straight
 * to Shelf's kernel, over HTTPS (the session's cookie is secure), and the test database with two
 * members and one book. Each test's changes are rolled back at its end (dama/doctrine-test-bundle),
 * so every test starts from these three rows.
 */
abstract class ShelfWebTestCase extends WebTestCase
{
    protected const string AMIRA = 'tea kettle under the lemon tree';
    protected const string JONAS = 'river stones in the morning light';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient(server: ['HTTPS' => 'on']);

        $covers = $_SERVER['SHELF_COVERS'] ?? null; // from .env.test
        self::assertIsString($covers);
        new Filesystem()->remove($this->client->getKernel()->getProjectDir() . '/' . $covers);
        self::service(CacheItemPoolInterface::class, 'rate_limiter.test')->clear(); // chapter 82: no email sent yet

        self::service(BookRepository::class)->add(new Book('Kindred', 'Octavia E. Butler', 1979, 2, new Isbn('9780807083697')));
        $members = self::service(MemberRepository::class);
        foreach (['Amira' => [Role::Reader, self::AMIRA], 'Jonas' => [Role::Staff, self::JONAS]] as $name => [$role, $password]) {
            $members->add(new Member($name, $role));
            $members->changePasswordHash($name, password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]));
        }
    }

    /** Logged in as this member, without the login form: for the tests of what comes after it. */
    protected function logIn(string $name): void
    {
        $this->client->loginUser(self::service(MemberRepository::class)->find($name) ?? self::fail("No member $name"));
    }

    /**
     * A service from the test container, which also holds the services that the application keeps private.
     * $name, when the service's name isn't its class.
     *
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    protected static function service(string $id, ?string $name = null): object
    {
        $service = static::getContainer()->get($name ?? $id);
        self::assertInstanceOf($id, $service);

        return $service;
    }
}
