<?php

declare(strict_types=1);

// Exercise 71, beginner: tests for an API.
//
// book.php is right. Write tests for bookAnswer(): the status, the Content-Type, and the JSON,
// for a book that exists and for one that doesn't. Each file in bugs/ is book.php with one mistake,
// and your tests must fail on every one. Compare the decoded JSON, not the text: a space doesn't matter.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/book.php';

final class BookTest extends TestCase
{
    public function testAKnownBook(): void
    {
        $answer = bookAnswer('Kindred');

        self::assertSame([200, 'application/json'], [$answer['status'], $answer['headers']['Content-Type'] ?? null]);
        self::assertSame(['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979], json_decode($answer['body'], true));
    }

    public function testATitleInAnyCase(): void
    {
        self::assertSame(200, bookAnswer(' KITCHEN ')['status']);
    }

    public function testAnUnknownBookIsAProblem(): void
    {
        $answer = bookAnswer('Sula');

        self::assertSame([404, 'application/problem+json'], [$answer['status'], $answer['headers']['Content-Type'] ?? null]);
        self::assertSame(
            ['type' => 'about:blank', 'title' => 'Not Found', 'status' => 404, 'detail' => 'There is no book called "Sula".'],
            json_decode($answer['body'], true),
        );
    }
}

// Check your work: composer exercise 71 beginner
