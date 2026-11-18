<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Queue.php');

final class QueueTest extends TestCase
{
    private const int NOW = 1_773_475_200; // 2026-03-14 08:00 UTC

    private PDO $db;
    private Queue $a;
    private Queue $b;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec('CREATE TABLE messages (id INTEGER PRIMARY KEY, body TEXT NOT NULL, tries INTEGER NOT NULL DEFAULT 0,
            available_at INTEGER NOT NULL, taken_by TEXT, failed INTEGER NOT NULL DEFAULT 0)');
        $this->a = new Queue($this->db, 'worker A');
        $this->b = new Queue($this->db, 'worker B');
    }

    public function testOldestFirstAndEachToOneWorker(): void
    {
        $first = $this->a->push('remind Amira', self::NOW);
        $second = $this->a->push('remind Ben', self::NOW);

        self::assertSame(['id' => $first, 'body' => 'remind Amira'], $this->a->take(self::NOW));
        self::assertSame(['id' => $second, 'body' => 'remind Ben'], $this->b->take(self::NOW));
        self::assertNull($this->a->take(self::NOW), 'Both are taken');

        $this->a->done($first);
        self::assertSame(1, (int) $this->db->query('SELECT COUNT(*) FROM messages')->fetchColumn());
    }

    public function testAMessageAnotherWorkerClaimedIsSkipped(): void
    {
        $this->a->push('remind Amira', self::NOW);
        $this->a->push('remind Ben', self::NOW);
        // Worker B was quicker: between A's SELECT and A's UPDATE, B took Amira's message.
        $this->db->exec("CREATE TRIGGER quicker BEFORE UPDATE OF taken_by ON messages
            WHEN NEW.taken_by = 'worker A' AND OLD.body = 'remind Amira'
            BEGIN UPDATE messages SET taken_by = 'worker B' WHERE id = OLD.id; SELECT RAISE(IGNORE); END");

        self::assertSame('remind Ben', $this->a->take(self::NOW)['body'] ?? null);
    }

    public function testAFailedMessageWaitsLongerEachTimeThenGivesUp(): void
    {
        $id = $this->a->push('remind Amira', self::NOW);

        $this->a->take(self::NOW);
        self::assertTrue($this->a->fail($id, self::NOW));
        self::assertNull($this->b->take(self::NOW + 59), 'Not before a minute');
        self::assertSame($id, $this->b->take(self::NOW + 60)['id'] ?? null);

        self::assertTrue($this->b->fail($id, self::NOW + 60));
        self::assertNull($this->a->take(self::NOW + 179), 'Two minutes this time');
        $this->a->take(self::NOW + 180);

        self::assertFalse($this->a->fail($id, self::NOW + 180), 'The third try failed');
        self::assertNull($this->a->take(self::NOW + 100_000), 'Failed for good: never again');
    }

    public function testOnlyTheWorkerThatHoldsAMessageFinishesIt(): void
    {
        $id = $this->a->push('remind Amira', self::NOW);
        $this->a->take(self::NOW);

        $this->expectException(QueueError::class);

        $this->b->done($id);
    }

    public function testAMessageNobodyTookCantFail(): void
    {
        $id = $this->a->push('remind Amira', self::NOW);

        $this->expectException(QueueError::class);

        $this->a->fail($id, self::NOW);
    }
}
