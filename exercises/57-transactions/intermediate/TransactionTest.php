<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'transaction.php');

final class TransactionTest extends TestCase
{
    public function testTheWorkIsSavedAndItsResultReturned(): void
    {
        $db = testLibrary();

        $result = inTransaction($db, function () use ($db): string {
            $db->exec("UPDATE books SET copies = 5 WHERE title = 'Kitchen'");
            self::assertTrue($db->inTransaction());

            return 'done';
        });

        self::assertSame('done', $result);
        self::assertFalse($db->inTransaction());
        self::assertSame('Kindred 0, Sula 2, Kitchen 5, Beloved 3; loans 1, 2, 3', stateOf($db));
    }

    public function testAFailureUndoesEverything(): void
    {
        $db = testLibrary();
        $problem = new RuntimeException('The printer is out of paper');

        try {
            inTransaction($db, function () use ($db, $problem): void {
                $db->exec("UPDATE books SET copies = 5 WHERE title = 'Kitchen'");
                $db->exec('DELETE FROM loans');

                throw $problem;
            });
            self::fail('The exception must go on');
        } catch (RuntimeException $e) {
            self::assertSame($problem, $e);
        }
        self::assertFalse($db->inTransaction());
        self::assertSame('Kindred 0, Sula 2, Kitchen 1, Beloved 3; loans 1, 2, 3', stateOf($db));
    }

    public function testADatabaseErrorUndoesEverythingToo(): void
    {
        $db = testLibrary();

        try {
            inTransaction($db, function () use ($db): void {
                $db->exec("UPDATE books SET copies = 5 WHERE title = 'Kitchen'");
                $db->exec("UPDATE books SET copies = -1 WHERE title = 'Sula'");
            });
        } catch (PDOException) {
        }
        self::assertSame('Kindred 0, Sula 2, Kitchen 1, Beloved 3; loans 1, 2, 3', stateOf($db));
    }
}
