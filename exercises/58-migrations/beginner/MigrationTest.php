<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';

final class MigrationTest extends TestCase
{
    public function testTheMembersKeepTheirDataAndGetTheNewColumns(): void
    {
        $db = testLibrary();

        migrate($db, subject(__DIR__, '004-member-details.sql'));

        self::assertSame([
            ['id' => 1, 'name' => 'Amira', 'email' => null, 'joined' => '2026-01-01'],
            ['id' => 2, 'name' => 'Jonas', 'email' => null, 'joined' => '2026-01-01'],
        ], rowsOf($db, 'SELECT id, name, email, joined FROM members ORDER BY id'));
    }

    public function testANewMemberCanHaveBoth(): void
    {
        $db = testLibrary();
        migrate($db, subject(__DIR__, '004-member-details.sql'));

        $db->exec("INSERT INTO members (name, email, joined) VALUES ('Kenji', 'kenji@example.org', '2026-03-02')");

        self::assertSame(
            [['email' => 'kenji@example.org', 'joined' => '2026-03-02']],
            rowsOf($db, "SELECT email, joined FROM members WHERE name = 'Kenji'"),
        );
    }

    public function testJoinedIsRequired(): void
    {
        $db = testLibrary();
        migrate($db, subject(__DIR__, '004-member-details.sql'));

        $this->expectException(PDOException::class);

        $db->exec("INSERT INTO members (name, joined) VALUES ('Kenji', NULL)");
    }
}
