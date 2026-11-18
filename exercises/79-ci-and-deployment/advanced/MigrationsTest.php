<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'migrations.php');

final class MigrationsTest extends TestCase
{
    public function testAMigrationThatOnlyAddsNeedsNoCare(): void
    {
        $sql = <<<'SQL'
            CREATE TABLE reviews (id INTEGER PRIMARY KEY, book_id INTEGER NOT NULL, text TEXT NOT NULL);
            CREATE INDEX idx_reviews_book ON reviews (book_id);
            ALTER TABLE books ADD COLUMN subtitle VARCHAR(255) DEFAULT NULL;
            UPDATE books SET subtitle = '' WHERE subtitle IS NULL;
            DELETE FROM api_tokens WHERE expires < CURRENT_TIMESTAMP
            SQL;

        self::assertSame([], needsCare($sql));
    }

    public function testEveryKindOfStatementThatNeedsCare(): void
    {
        $sql = <<<'SQL'
            DROP TABLE old_loans;
            drop index idx_books_title;
            ALTER TABLE members DROP COLUMN email;
            ALTER TABLE books RENAME COLUMN year TO published;
            RENAME TABLE loans TO lendings;
            TRUNCATE api_tokens;
            DELETE FROM loans;
            UPDATE books SET copies = 0
            SQL;

        self::assertSame([
            'DROP TABLE old_loans',
            'drop index idx_books_title',
            'ALTER TABLE members DROP COLUMN email',
            'ALTER TABLE books RENAME COLUMN year TO published',
            'RENAME TABLE loans TO lendings',
            'TRUNCATE api_tokens',
            'DELETE FROM loans',
            'UPDATE books SET copies = 0',
        ], needsCare($sql));
    }

    public function testOneLineEach(): void
    {
        self::assertSame(['DELETE FROM loans'], needsCare("DELETE\n    FROM   loans ;\n"));
    }

    public function testWordsInStringsAreNotSql(): void
    {
        $sql = "INSERT INTO notes (text) VALUES ('Never DROP TABLE books; really'); "
            . "UPDATE books SET author = 'O''Brien; where?' WHERE title = 'Kindred'; "
            . "UPDATE books SET note = 'no WHERE here'";

        self::assertSame(["UPDATE books SET note = 'no WHERE here'"], needsCare($sql));
    }

    public function testCommentsAreNotSql(): void
    {
        $sql = <<<'SQL'
            -- DROP TABLE books; was the old plan
            /* TRUNCATE loans; no: the loans stay */
            CREATE TABLE tags (id INTEGER PRIMARY KEY);
            DELETE FROM tags -- every row; there are none yet
            ;
            UPDATE books /* every book */ SET copies = 1 WHERE id = 3
            SQL;

        self::assertSame(['DELETE FROM tags'], needsCare($sql));
    }
}
