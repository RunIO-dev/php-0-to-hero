<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoansTest extends TestCase
{
    public function testLendingChangesTheCallersBooks(): void
    {
        require_once subject(__DIR__, 'loans.php');

        $kindred = new Book();
        $kindred->title = 'Kindred';
        $kindred->copies = 2;
        $kitchen = new Book();
        $kitchen->title = 'Kitchen';
        $kitchen->copies = 0;

        lendAll([$kindred, $kitchen]);

        self::assertSame(1, $kindred->copies);
        self::assertSame(0, $kitchen->copies);
    }

    public function testACopyKeepsTheStateBefore(): void
    {
        require_once subject(__DIR__, 'loans.php');

        $book = new Book();
        $book->title = 'Kindred';
        $book->copies = 2;

        $saved = before($book);
        lendAll([$book]);

        self::assertNotSame($book, $saved);
        self::assertSame(2, $saved->copies);
        self::assertSame('Kindred', $saved->title);
        self::assertSame(1, $book->copies);
    }
}
