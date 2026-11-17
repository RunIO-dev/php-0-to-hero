<?php

declare(strict_types=1);

// Exercise 71, intermediate: testing who may do what.
//
// notes.php is right. Write tests for notesAnswer(): what each member sees, what happens without
// a token or with a wrong one, and deleting a note: your own, someone else's, one that doesn't exist.
// Each file in bugs/ is notes.php with one mistake, and your tests must fail on every one.
// The refusals matter most: a test for "Amira may not delete Jonas's note" fails the day someone removes the check.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/notes.php';

final class NotesTest extends TestCase
{
    public function testEachMemberSeesTheirOwnNotes(): void
    {
        $amira = notesAnswer('GET', 'Bearer amira-token');

        self::assertSame([200, 'application/json'], [$amira['status'], $amira['headers']['Content-Type'] ?? null]);
        self::assertSame([['id' => 1, 'text' => 'Read chapter 3 again.']], json_decode($amira['body'], true));
        self::assertSame([['id' => 2, 'text' => 'Order a second copy.']], json_decode(notesAnswer('GET', 'Bearer jonas-token')['body'], true));
    }

    public function testWithoutAKnownTokenTheAnswerIs401(): void
    {
        foreach (['', 'Bearer nobody-token', 'Bearer amira-tokens', 'Bearer amira-anything', 'Basic YW1pcmE6dGVh'] as $header) {
            $answer = notesAnswer('GET', $header);

            self::assertSame([401, 'Bearer', 'application/problem+json'], [$answer['status'], $answer['headers']['WWW-Authenticate'] ?? null, $answer['headers']['Content-Type'] ?? null], $header);
        }
        self::assertSame(401, notesAnswer('DELETE', '', 1)['status']);
    }

    public function testAMemberDeletesTheirOwnNote(): void
    {
        self::assertSame(['status' => 204, 'headers' => [], 'body' => ''], notesAnswer('DELETE', 'Bearer amira-token', 1));
    }

    public function testSomeoneElsesNoteLooksMissing(): void
    {
        $theirs = notesAnswer('DELETE', 'Bearer amira-token', 2);
        $missing = notesAnswer('DELETE', 'Bearer amira-token', 9);

        self::assertSame(404, $theirs['status']);
        self::assertSame([$missing['status'], $missing['headers']], [$theirs['status'], $theirs['headers']]);
    }
}

// Check your work: composer exercise 71 intermediate
