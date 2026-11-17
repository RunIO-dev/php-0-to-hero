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
        self::markTestIncomplete('Write this test, then the others.');
    }

    // your other tests here
}

// Check your work: composer exercise 71 intermediate
