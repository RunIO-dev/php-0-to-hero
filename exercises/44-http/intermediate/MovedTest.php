<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MovedTest extends TestCase
{
    public function testOldPagesMoved(): void
    {
        [$list, $join] = responses_of(dirname(subject(__DIR__, 'moved.php')), "GET /moved.php?page=list\n###\nGET /moved.php?page=join\n");

        self::assertSame(301, $list['status']);
        self::assertSame('/books.php', $list['headers']['location'] ?? null);
        self::assertSame(301, $join['status']);
        self::assertSame('/members/join.php', $join['headers']['location'] ?? null);
    }

    public function testEverythingElseIsNotFound(): void
    {
        [$other, $none] = responses_of(dirname(subject(__DIR__, 'moved.php')), "GET /moved.php?page=contact\n###\nGET /moved.php\n");

        self::assertSame(404, $other['status']);
        self::assertSame("Not found\n", $other['body']);
        self::assertArrayNotHasKey('location', $other['headers']);
        self::assertSame(404, $none['status']);
    }
}
