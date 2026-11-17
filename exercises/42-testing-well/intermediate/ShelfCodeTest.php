<?php

declare(strict_types=1);

// Exercise 42, intermediate: one test, many cases.
//
// These four tests are the same test with other data. Replace them with one test
// method and a data provider, with a name for each case. Keep all four cases, and
// add one with an accent in the last name (such as "Gabriel García Márquez", 1967),
// so that your tests catch every bug in bugs/.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/shelf-code.php';

final class ShelfCodeTest extends TestCase
{
    public function testButler(): void
    {
        self::assertSame('BUT-1979', shelfCode('Octavia E. Butler', 1979));
    }

    public function testAchebe(): void
    {
        self::assertSame('ACH-1958', shelfCode('Chinua Achebe', 1958));
    }

    public function testYoshimoto(): void
    {
        self::assertSame('YOS-1988', shelfCode('Banana Yoshimoto', 1988));
    }

    public function testOneName(): void
    {
        self::assertSame('HOM-800', shelfCode('Homer', 800));
    }
}

// Check your work: composer exercise 42 intermediate
