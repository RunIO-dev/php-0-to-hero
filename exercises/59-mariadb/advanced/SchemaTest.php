<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../databases.php';
require_once subject(__DIR__, 'schema.php');

final class SchemaTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function databases(): iterable
    {
        yield 'SQLite' => ['sqlite'];
        yield 'MariaDB' => ['mariadb'];
    }

    private function database(string $which): PDO
    {
        if ($which === 'sqlite') {
            $db = sqlite();
        } else {
            $db = mariadb('shelf_admin');
            $db->exec('DROP TABLE IF EXISTS exercise_books');
        }
        createBooks($db);

        return $db;
    }

    #[DataProvider('databases')]
    public function testTheDatabaseChoosesIdsAndCopies(string $which): void
    {
        $db = $this->database($which);
        $db->exec("INSERT INTO exercise_books (title) VALUES ('Kindred')");
        $db->exec("INSERT INTO exercise_books (title, copies) VALUES ('موسم الهجرة إلى الشمال', 2)");

        self::assertSame([
            ['id' => 1, 'title' => 'Kindred', 'copies' => 1],
            ['id' => 2, 'title' => 'موسم الهجرة إلى الشمال', 'copies' => 2],
        ], rowsOf($db, 'SELECT id, title, copies FROM exercise_books ORDER BY id'));
    }

    /** @return iterable<string, array{string, string}> */
    public static function refused(): iterable
    {
        foreach (['sqlite', 'mariadb'] as $which) {
            yield "$which: a title in other letters" => [$which, "INSERT INTO exercise_books (title) VALUES ('KINDRED')"];
            yield "$which: no title" => [$which, 'INSERT INTO exercise_books (copies) VALUES (1)'];
            yield "$which: fewer than none" => [$which, "INSERT INTO exercise_books (title, copies) VALUES ('Sula', -1)"];
            yield "$which: text as copies" => [$which, "INSERT INTO exercise_books (title, copies) VALUES ('Sula', 'two')"];
        }
    }

    #[DataProvider('refused')]
    public function testTheRulesHold(string $which, string $sql): void
    {
        $db = $this->database($which);
        $db->exec("INSERT INTO exercise_books (title) VALUES ('Kindred')");

        $this->expectException(PDOException::class);

        $db->exec($sql);
    }
}
