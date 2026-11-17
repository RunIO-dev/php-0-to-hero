<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FormatsTest extends TestCase
{
    public function testJsonWhenAskedFor(): void
    {
        [$json] = responses_of(dirname(subject(__DIR__, 'formats.php')), "GET /formats.php\nAccept: application/json\n");

        self::assertSame('application/json', $json['headers']['content-type'] ?? null);
        self::assertSame([['title' => 'Kindred', 'year' => 1979], ['title' => 'Sula', 'year' => 1973]], json_decode($json['body'], true));
    }

    public function testPlainTextOtherwise(): void
    {
        [$browser, $none] = responses_of(dirname(subject(__DIR__, 'formats.php')), "GET /formats.php\nAccept: text/html,*/*\n###\nGET /formats.php\n");

        self::assertSame('text/plain; charset=UTF-8', $browser['headers']['content-type'] ?? null);
        self::assertSame("Kindred (1979)\nSula (1973)\n", $browser['body']);
        self::assertSame("Kindred (1979)\nSula (1973)\n", $none['body']);
    }
}
