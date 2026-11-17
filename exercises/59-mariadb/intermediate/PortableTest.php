<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../databases.php';
require_once subject(__DIR__, 'portable.php');

final class PortableTest extends TestCase
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
            $db->exec('CREATE TABLE exercise_copies (title TEXT NOT NULL UNIQUE, copies INTEGER NOT NULL)');

            return $db;
        }
        mariadb('shelf_admin')->exec('DROP TABLE IF EXISTS exercise_copies;
            CREATE TABLE exercise_copies (title VARCHAR(200) NOT NULL UNIQUE, copies INT NOT NULL)');

        return mariadb('shelf_app');
    }

    #[DataProvider('databases')]
    public function testANewTitleIsAddedAndAKnownOneChanged(string $which): void
    {
        $db = $this->database($which);

        saveCopies($db, 'Kindred', 1);
        saveCopies($db, 'Sula', 2);
        saveCopies($db, 'Kindred', 3);

        self::assertSame(
            [['title' => 'Kindred', 'copies' => 3], ['title' => 'Sula', 'copies' => 2]],
            rowsOf($db, 'SELECT title, copies FROM exercise_copies ORDER BY title'),
        );
    }
}
