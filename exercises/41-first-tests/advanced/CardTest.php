<?php

declare(strict_types=1);

// Exercise 41, advanced: tests for a class.
//
// card.php is right. Write tests for Card: when it is valid, and what renew()
// returns, for a card that is still valid and for one that has expired. Each file
// in bugs/ is card.php with one mistake, and your tests must fail on every one.
// Think about the edges: the last day of a card is the day most likely to be wrong.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/card.php';

final class CardTest extends TestCase
{
    public function testValidUpToAndIncludingTheLastDay(): void
    {
        self::markTestIncomplete('Write this test, then the others.');
    }

    // your other tests here
}

// Check your work: composer exercise 41 advanced
