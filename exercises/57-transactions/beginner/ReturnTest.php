<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'return.php');

final class ReturnTest extends TestCase
{
    public function testTheCopyComesBackAndTheLoanGoes(): void
    {
        $db = testLibrary();

        returnBook($db, 1);

        self::assertSame('Kindred 1, Sula 2, Kitchen 1, Beloved 3; loans 2, 3', stateOf($db));
    }

    public function testAnUnknownLoanChangesNothing(): void
    {
        $db = testLibrary();

        try {
            returnBook($db, 99);
            self::fail('An unknown loan must throw');
        } catch (OutOfBoundsException) {
        }
        self::assertSame('Kindred 0, Sula 2, Kitchen 1, Beloved 3; loans 1, 2, 3', stateOf($db));
        self::assertFalse($db->inTransaction(), 'Finish the transaction, also when it fails');
    }
}
