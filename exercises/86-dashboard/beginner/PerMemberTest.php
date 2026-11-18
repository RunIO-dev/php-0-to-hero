<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'perMember.php');

final class PerMemberTest extends TestCase
{
    /** @param list<array{string, string, string}> $loans title, member, lent on */
    private function db(array $loans): PDO
    {
        $db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $db->exec('CREATE TABLE loans (id INTEGER PRIMARY KEY, title TEXT, member TEXT, lent_on TEXT)');
        $insert = $db->prepare('INSERT INTO loans (title, member, lent_on) VALUES (?, ?, ?)');
        foreach ($loans as $loan) {
            $insert->execute($loan);
        }

        return $db;
    }

    public function testTheMostFirstThenAToZ(): void
    {
        $db = $this->db([
            ['Kindred', 'Ben', '2026-01-05'], ['Sula', 'Amira', '2026-01-09'], ['Beloved', 'Ben', '2026-02-02'],
            ['Kindred', 'Chen', '2026-02-11'], ['Sula', 'Ben', '2026-03-01'], ['Beloved', 'Chen', '2026-03-04'],
        ]);

        self::assertSame(['Ben' => 3, 'Chen' => 2, 'Amira' => 1], loansPerMember($db));
    }

    public function testEqualCountsAreInAlphabeticalOrder(): void
    {
        $db = $this->db([['Kindred', 'Chen', '2026-01-05'], ['Sula', 'Amira', '2026-01-09']]);

        self::assertSame(['Amira' => 1, 'Chen' => 1], loansPerMember($db));
    }

    public function testNoLoansNoMembers(): void
    {
        self::assertSame([], loansPerMember($this->db([])));
    }
}
