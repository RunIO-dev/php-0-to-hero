<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HealthTest extends TestCase
{
    public function testHealthy(): void
    {
        [$ok] = responses_of(dirname(subject(__DIR__, 'health.php')), "GET /health.php\n");

        self::assertSame(200, $ok['status']);
        self::assertSame('application/json', $ok['headers']['content-type'] ?? null);
        self::assertSame(['status' => 'ok', 'books' => 1], json_decode($ok['body'], true));
    }

    public function testDownWhenTheFileIsMissing(): void
    {
        [$down] = responses_of(dirname(subject(__DIR__, 'health.php')), "# env BOOKS_FILE=missing.json\nGET /health.php\n");

        self::assertSame(503, $down['status']);
        $body = json_decode($down['body'], true);
        self::assertIsArray($body);
        self::assertSame('down', $body['status'] ?? null);
        self::assertIsString($body['reason'] ?? null);
    }

    public function testDownWhenTheFileIsNotAList(): void
    {
        [$down] = responses_of(dirname(subject(__DIR__, 'health.php')), "# env BOOKS_FILE=HealthTest.php\nGET /health.php\n");

        self::assertSame(503, $down['status']);
    }
}
