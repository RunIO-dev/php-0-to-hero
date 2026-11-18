<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Report.php');

final class ReportTest extends TestCase
{
    private PDO $db;
    private Report $report;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec("CREATE TABLE books (id INTEGER PRIMARY KEY, title TEXT);
            CREATE TABLE loans (id INTEGER PRIMARY KEY, book_id INTEGER, member TEXT, lent_on TEXT, due TEXT, returned_on TEXT);
            INSERT INTO books VALUES (1, 'Kindred'), (2, 'Sula'), (3, 'Beloved'), (4, 'Americanah');
            INSERT INTO loans (book_id, member, lent_on, due, returned_on) VALUES
                (1, 'Amira', '2026-01-05', '2026-01-19', '2026-01-15'),
                (1, 'Ben',   '2026-02-02', '2026-02-16', '2026-02-18'),
                (2, 'Ben',   '2026-02-20', '2026-03-06', '2026-03-06'),
                (4, 'Chen',  '2026-02-21', '2026-03-07', '2026-03-12'),
                (1, 'Amira', '2026-03-02', '2026-03-16', NULL)");
        $this->report = new Report($this->db);
    }

    public function testTheMostPopularFirstThenAToZ(): void
    {
        self::assertSame(['Kindred' => 3, 'Americanah' => 1, 'Sula' => 1], $this->report->popular('2026-01-01', 10));
        self::assertSame(['Kindred' => 2, 'Americanah' => 1], $this->report->popular('2026-02-01', 2));
        self::assertSame([], $this->report->popular('2026-04-01', 10));
    }

    public function testOnTimeCountsTheLateOnes(): void
    {
        self::assertSame(['returned' => 4, 'late' => 2, 'percent' => 50], $this->report->onTime('2026-01-01'));
        self::assertSame(['returned' => 2, 'late' => 1, 'percent' => 50], $this->report->onTime('2026-03-01'));
        self::assertSame(['returned' => 0, 'late' => 0, 'percent' => null], $this->report->onTime('2026-04-01'), 'Nothing came back');
    }

    public function testUnborrowedIncludesBooksNeverLent(): void
    {
        self::assertSame(['Beloved'], $this->report->unborrowed('2026-01-01'));
        self::assertSame(['Americanah', 'Beloved', 'Sula'], $this->report->unborrowed('2026-02-25'));
    }

    public function testValuesAreParameters(): void
    {
        // As SQL, OR '1'='1' would match every loan; as a value, it is a date after every loan.
        self::assertSame([], $this->report->popular("9999' OR '1'='1", 10));
        self::assertSame(1, (int) $this->db->query('SELECT COUNT(*) FROM books WHERE id = 1')->fetchColumn());
    }
}
