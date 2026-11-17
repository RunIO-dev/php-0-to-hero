<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'throttle.php');

final class ThrottleTest extends TestCase
{
    private PDO $db;
    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec('CREATE TABLE login_failures (name TEXT NOT NULL, at TEXT NOT NULL)');
        $this->now = new DateTimeImmutable('2026-03-16 10:00:00');
    }

    public function testFiveFailuresBlockTheName(): void
    {
        $this->fail4('Amira');
        self::assertFalse(isBlocked($this->db, 'Amira', $this->now), 'Four failures are allowed');

        recordFailure($this->db, 'AMIRA', $this->now);

        self::assertTrue(isBlocked($this->db, 'amira', $this->now));
        self::assertFalse(isBlocked($this->db, 'Jonas', $this->now), 'Other names are not blocked');
    }

    public function testOnlyTheLast15MinutesCount(): void
    {
        foreach (range(1, 5) as $try) {
            recordFailure($this->db, 'Amira', $this->now);
        }

        self::assertTrue(isBlocked($this->db, 'Amira', $this->now->modify('+14 minutes')));
        self::assertFalse(isBlocked($this->db, 'Amira', $this->now->modify('+15 minutes')));
    }

    public function testASuccessfulLoginStartsTheCountAgain(): void
    {
        $this->fail4('Amira');

        forgetFailures($this->db, 'amira');
        recordFailure($this->db, 'Amira', $this->now);

        self::assertFalse(isBlocked($this->db, 'Amira', $this->now));
        $rows = $this->db->query("SELECT COUNT(*) FROM login_failures");
        self::assertSame(1, $rows === false ? 0 : (int) $rows->fetchColumn());
    }

    private function fail4(string $name): void
    {
        foreach (range(1, 4) as $minute) {
            recordFailure($this->db, $name, $this->now->modify("-$minute minutes"));
        }
    }
}
