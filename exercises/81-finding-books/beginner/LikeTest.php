<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'like.php');

final class LikeTest extends TestCase
{
    public function testThePatternFindsThePartAnywhereInAnyCase(): void
    {
        self::assertSame('%butler%', likePattern('Butler'));
        self::assertSame('%élan%', likePattern('ÉLAN'));
    }

    public function testWildcardsTheVisitorTypesAreLetters(): void
    {
        self::assertSame('%50\%%', likePattern('50%'));
        self::assertSame('%o\_e%', likePattern('o_e'));
        self::assertSame('%a\\\\b%', likePattern('a\b'));
    }

    public function testADatabaseFindsOnlyWhatWasTyped(): void
    {
        $db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $db->exec("CREATE TABLE books (title TEXT); INSERT INTO books VALUES ('Beloved'), ('50% Off'), ('Sula')");
        $find = $db->prepare("SELECT title FROM books WHERE LOWER(title) LIKE :pattern ESCAPE '\\' ORDER BY title");

        $find->execute(['pattern' => likePattern('%')]);
        self::assertSame(['50% Off'], $find->fetchAll(PDO::FETCH_COLUMN));
        $find->execute(['pattern' => likePattern('o_e')]);
        self::assertSame([], $find->fetchAll(PDO::FETCH_COLUMN), 'Without the escape, _ would find "Beloved"');
        $find->execute(['pattern' => likePattern('SUL')]);
        self::assertSame(['Sula'], $find->fetchAll(PDO::FETCH_COLUMN));
    }
}
