<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use LogicException;
use RuntimeException;
use SQLite3;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Shelf's backups (chapter 88): each one a folder in SHELF_DATA/backups/, named by its time, with a
 * copy of the database and of the covers. The worker makes one every night; the newest KEEP stay.
 *   php bin/console shelf:backup
 * A copy on the same disk helps after a mistake, not after the disk is lost: copy the folder to
 * another computer too (the launch checklist).
 */
final readonly class Backups
{
    public const int KEEP = 7;

    private const string NAME = 'Y-m-d\THis';

    public function __construct(
        private Connection $db,
        #[Autowire('%kernel.project_dir%/%env(SHELF_DATA)%/backups')]
        private string $dir,
        #[Autowire('%kernel.project_dir%/%env(SHELF_COVERS)%')]
        private string $covers,
        private Filesystem $files,
    ) {}

    /** A new backup, checked; then the oldest beyond KEEP are deleted. Returns the new folder. */
    public function make(DateTimeImmutable $now): string
    {
        $database = $this->db->getParams()['path'] ?? null;
        if (!$this->db->getDatabasePlatform() instanceof SQLitePlatform || !is_string($database)) {
            throw new LogicException('shelf:backup copies an SQLite database file; for MariaDB, use mariadb-dump.');
        }
        $folder = "$this->dir/" . $now->format(self::NAME);
        $this->files->mkdir($folder, 0o700); // the members' names and emails: for this user only
        // SQLite's backup API copies the database page by page, as one consistent state, while the
        // app and the worker go on writing. copy() of the file could catch it halfway through a write.
        $from = new SQLite3($database, SQLITE3_OPEN_READONLY);
        $to = new SQLite3("$folder/shelf.sqlite");
        if (!$from->backup($to) || $to->querySingle('PRAGMA integrity_check') !== 'ok') {
            throw new RuntimeException("The backup in $folder failed SQLite's integrity check");
        }
        $from->close();
        $to->close();
        // Always a covers folder, even an empty one: a restore copies it back as it is.
        $this->files->mkdir("$folder/covers", 0o700);
        if (is_dir($this->covers)) {
            $this->files->mirror($this->covers, "$folder/covers");
        }
        foreach (array_slice($this->all(), self::KEEP) as $old) {
            $this->files->remove("$this->dir/" . $old->format(self::NAME));
        }

        return $folder;
    }

    /** When the newest backup was made, or null if there is none. */
    public function latest(): ?DateTimeImmutable
    {
        return $this->all()[0] ?? null;
    }

    /** @return list<DateTimeImmutable> every backup's time, the newest first */
    private function all(): array
    {
        $times = [];
        foreach ((is_dir($this->dir) ? scandir($this->dir) : false) ?: [] as $name) {
            $time = DateTimeImmutable::createFromFormat('!' . self::NAME, $name);
            if ($time !== false && $time->format(self::NAME) === $name) { // only folders named by a time
                $times[] = $time;
            }
        }
        rsort($times);

        return $times;
    }
}
