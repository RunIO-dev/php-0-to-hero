<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Health.php');

/** A database server that is down: every query fails, with a message that names the server. */
final class DeadDatabase extends PDO
{
    public function __construct()
    {
        parent::__construct('sqlite::memory:');
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        throw new PDOException('SQLSTATE[HY000] [2002] Connection refused: db:3306 as shelf_app');
    }
}

final class HealthTest extends TestCase
{
    public function testADatabaseThatAnswers(): void
    {
        $response = new Health(new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]))();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['status' => 'ok'], json_decode((string) $response->getContent(), true));
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function testADatabaseThatIsDown(): void
    {
        $response = new Health(new DeadDatabase())();

        self::assertSame(503, $response->getStatusCode());
        self::assertSame('application/problem+json', $response->headers->get('Content-Type'));
        self::assertSame(
            ['type' => 'about:blank', 'title' => 'Service Unavailable', 'status' => 503, 'detail' => 'The database does not answer.'],
            json_decode((string) $response->getContent(), true),
        );
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function testTheErrorNeverLeaks(): void
    {
        $body = (string) new Health(new DeadDatabase())()->getContent();

        self::assertStringNotContainsString('shelf_app', $body);
        self::assertStringNotContainsString('3306', $body);
    }
}
