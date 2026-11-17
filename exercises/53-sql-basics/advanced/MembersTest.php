<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MembersTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->exec((string) file_get_contents(subject(__DIR__, 'members.sql')));
        $this->db->exec("INSERT INTO members (name, role, joined) VALUES ('Jonas', 'staff', '2025-11-04')");
    }

    public function testAReaderUnlessTheRoleIsGiven(): void
    {
        $this->db->exec("INSERT INTO members (name, joined) VALUES ('Amira', '2026-03-02')");
        $result = $this->db->query('SELECT id, name, role, joined FROM members ORDER BY id');

        self::assertSame([
            ['id' => 1, 'name' => 'Jonas', 'role' => 'staff', 'joined' => '2025-11-04'],
            ['id' => 2, 'name' => 'Amira', 'role' => 'reader', 'joined' => '2026-03-02'],
        ], $result === false ? [] : $result->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @return iterable<string, array{string}> */
    public static function refused(): iterable
    {
        yield 'the same name' => ["INSERT INTO members (name, joined) VALUES ('Jonas', '2026-03-02')"];
        yield 'the same name in other letters' => ["INSERT INTO members (name, joined) VALUES ('JONAS', '2026-03-02')"];
        yield 'no name' => ["INSERT INTO members (joined) VALUES ('2026-03-02')"];
        yield 'another role' => ["INSERT INTO members (name, role, joined) VALUES ('Ana', 'admin', '2026-03-02')"];
        yield 'no date' => ["INSERT INTO members (name) VALUES ('Ana')"];
        yield 'a date in another shape' => ["INSERT INTO members (name, joined) VALUES ('Ana', '2/3/2026')"];
    }

    #[DataProvider('refused')]
    public function testTheDatabaseRefuses(string $sql): void
    {
        $this->expectException(PDOException::class);

        $this->db->exec($sql);
    }
}
