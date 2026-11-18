<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Holds.php');

final class HoldsTest extends TestCase
{
    private PDO $db;
    private Holds $holds;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec('CREATE TABLE books (title TEXT PRIMARY KEY, copies INTEGER NOT NULL)');
        $this->db->exec("CREATE TABLE holds (id INTEGER PRIMARY KEY, title TEXT, member TEXT, status TEXT NOT NULL DEFAULT 'waiting')");
        $this->db->exec("INSERT INTO books VALUES ('Kindred', 0), ('Sula', 2)");
        $this->holds = new Holds($this->db);
    }

    public function testCopiesGoToTheQueueInOrder(): void
    {
        $this->holds->reserve('Kindred', 'Amira');
        $this->holds->reserve('Kindred', 'Ben');

        self::assertSame('Amira', $this->holds->copyBack('Kindred'));
        self::assertSame('Ben', $this->holds->copyBack('Kindred'));
        self::assertNull($this->holds->copyBack('Kindred'), 'Nobody waits any more');
        self::assertSame(1, $this->copies('Kindred'));
    }

    public function testABookOnTheShelfIsntReserved(): void
    {
        $this->expectException(HoldError::class);

        $this->holds->reserve('Sula', 'Amira');
    }

    public function testOneOpenReservationPerMemberAndBook(): void
    {
        $this->holds->reserve('Kindred', 'Amira');
        $this->holds->copyBack('Kindred'); // ready now: still open

        try {
            $this->holds->reserve('Kindred', 'Amira');
            self::fail('Reserved twice');
        } catch (HoldError) {
            self::assertSame(1, (int) $this->db->query('SELECT COUNT(*) FROM holds')->fetchColumn());
        }
    }

    public function testAReservationAnotherDeskClaimedIsSkipped(): void
    {
        $this->holds->reserve('Kindred', 'Amira');
        $this->holds->reserve('Kindred', 'Ben');
        // Another desk was quicker, between this desk's SELECT and its UPDATE.
        $this->db->exec("CREATE TRIGGER quicker BEFORE UPDATE ON holds WHEN OLD.member = 'Amira' AND NEW.status = 'ready'
            BEGIN UPDATE holds SET status = 'collected' WHERE id = OLD.id; SELECT RAISE(IGNORE); END");

        self::assertSame('Ben', $this->holds->copyBack('Kindred'));
        self::assertSame(0, $this->copies('Kindred'));
    }

    public function testACancelledCopyGoesOn(): void
    {
        $amira = $this->holds->reserve('Kindred', 'Amira');
        $ben = $this->holds->reserve('Kindred', 'Ben');
        $this->holds->copyBack('Kindred');

        self::assertNull($this->holds->cancel($ben), 'Ben had no copy yet');
        self::assertNull($this->holds->cancel($amira), 'Nobody else waits: on the shelf');
        self::assertSame(1, $this->copies('Kindred'));
    }

    public function testACancelledReservationStaysCancelled(): void
    {
        $id = $this->holds->reserve('Kindred', 'Amira');
        $this->holds->cancel($id);

        $this->expectException(HoldError::class);

        $this->holds->cancel($id);
    }

    public function testAFailedCancelChangesNothing(): void
    {
        $amira = $this->holds->reserve('Kindred', 'Amira');
        $this->holds->copyBack('Kindred');
        $this->db->exec('DROP TABLE books'); // the copy can't go back: the cancel must not stay half done

        try {
            $this->holds->cancel($amira);
            self::fail('No books table, no cancel');
        } catch (PDOException) {
            $status = $this->db->prepare('SELECT status FROM holds WHERE id = ?');
            $status->execute([$amira]);
            self::assertSame('ready', $status->fetchColumn());
        }
    }

    private function copies(string $title): int
    {
        $copies = $this->db->prepare('SELECT copies FROM books WHERE title = ?');
        $copies->execute([$title]);

        return (int) $copies->fetchColumn();
    }
}
