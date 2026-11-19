<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use DateTimeImmutable;
use Shelf\Backups;
use SQLite3;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The backups (chapter 88). The test's own rows are in its transaction, which a backup can't see:
 * it copies what the database has committed, the tables with their migrations.
 */
final class BackupsTest extends KernelTestCase
{
    private string $dir;

    protected function setUp(): void
    {
        self::bootKernel();
        $data = $_SERVER['SHELF_DATA'] ?? null; // from .env.test
        self::assertIsString($data);
        $this->dir = self::$kernel?->getProjectDir() . "/$data/backups";
        new Filesystem()->remove($this->dir);
    }

    public function testABackupIsACheckedCopyOfTheDatabase(): void
    {
        $folder = $this->backups()->make(new DateTimeImmutable('2026-03-16 02:30:00'));

        self::assertSame("$this->dir/2026-03-16T023000", $folder);
        $copy = new SQLite3("$folder/shelf.sqlite", SQLITE3_OPEN_READONLY);
        self::assertSame(0, $copy->querySingle('SELECT COUNT(*) FROM books'), 'The table is there');
    }

    public function testTheNewestSevenStay(): void
    {
        mkdir("$this->dir/notes", recursive: true); // not a backup: never deleted
        foreach (range(1, 9) as $day) {
            $this->backups()->make(new DateTimeImmutable("2026-03-0$day 02:30"));
        }

        $left = array_values(array_diff(scandir($this->dir) ?: [], ['.', '..']));

        self::assertSame(['2026-03-03T023000', '2026-03-04T023000', '2026-03-05T023000', '2026-03-06T023000',
            '2026-03-07T023000', '2026-03-08T023000', '2026-03-09T023000', 'notes'], $left);
        self::assertEquals(new DateTimeImmutable('2026-03-09 02:30'), $this->backups()->latest());
    }

    private function backups(): Backups
    {
        $backups = self::getContainer()->get(Backups::class);
        self::assertInstanceOf(Backups::class, $backups);

        return $backups;
    }
}
