<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ChangesTest extends TestCase
{
    public function testTheLibraryAfterTheChanges(): void
    {
        $changes = require subject(__DIR__, 'changes.php');
        $db = new PDO('sqlite::memory:');
        $db->exec((string) file_get_contents(__DIR__ . '/../library.sql'));
        foreach (['add', 'return', 'clear'] as $name) {
            $db->exec($changes[$name]);
        }
        $result = $db->query('SELECT title, author, year, copies FROM books ORDER BY title');

        self::assertSame([
            ['title' => 'Americanah', 'author' => 'Chimamanda Ngozi Adichie', 'year' => 2013, 'copies' => 2],
            ['title' => 'Beloved', 'author' => 'Toni Morrison', 'year' => 1987, 'copies' => 1],
            ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988, 'copies' => 1],
            ['title' => 'No Longer Human', 'author' => 'Osamu Dazai', 'year' => 1948, 'copies' => 1],
            ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => 1973, 'copies' => 3],
            ['title' => 'The Remains of the Day', 'author' => 'Kazuo Ishiguro', 'year' => 1989, 'copies' => 2],
            ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958, 'copies' => 2],
        ], $result === false ? [] : $result->fetchAll(PDO::FETCH_ASSOC));
    }
}
