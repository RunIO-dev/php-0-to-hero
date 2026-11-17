<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class NewBooksTest extends TestCase
{
    public function testTheListIsJson(): void
    {
        [$response] = responses_of(dirname(subject(__DIR__, 'new.php')), "GET /new.php\n");

        self::assertSame(200, $response['status']);
        self::assertSame('application/json', $response['headers']['content-type'] ?? null, 'Send Content-Type: application/json.');
        self::assertSame([
            ['title' => 'Cien años de soledad', 'author' => 'Gabriel García Márquez', 'cover' => '/cover?title=Cien'],
            ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'cover' => null],
        ], json_decode($response['body'], true, flags: JSON_THROW_ON_ERROR));
    }

    public function testItIsReadableWithSlashesAndLettersAsTheyAre(): void
    {
        [$response] = responses_of(dirname(subject(__DIR__, 'new.php')), "GET /new.php\n");

        self::assertStringContainsString("\n    {\n        \"title\": \"Cien años de soledad\",", $response['body'], 'Use JSON_PRETTY_PRINT and JSON_UNESCAPED_UNICODE.');
        self::assertStringContainsString('"/cover?title=Cien"', $response['body'], 'Use JSON_UNESCAPED_SLASHES.');
    }
}
