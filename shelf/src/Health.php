<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Throwable;

/**
 * Is Shelf working (chapter 88)? Three questions, each answered yes or no: does the database answer,
 * has the worker been seen lately, and is there a recent backup. /api/health asks them for a monitor,
 * shelf:launch for the person who puts Shelf online.
 */
final readonly class Health
{
    /** The heartbeat comes every 5 minutes: three missed ones, and the worker counts as stopped. */
    public const string WORKER_LATE = '15 minutes';

    /** A backup is made every night: a day and a few hours without one, and something is wrong. */
    public const string BACKUP_LATE = '26 hours';

    public function __construct(private Connection $db, private Heartbeat $heartbeat, private Backups $backups) {}

    /** @return array{database: bool, worker: bool, backup: bool} */
    public function check(DateTimeImmutable $now): array
    {
        return [
            'database' => $this->databaseAnswers(),
            'worker' => self::since($this->heartbeat->last(), $now, self::WORKER_LATE),
            'backup' => self::since($this->backups->latest(), $now, self::BACKUP_LATE),
        ];
    }

    private function databaseAnswers(): bool
    {
        try {
            $this->db->fetchOne('SELECT COUNT(*) FROM books');

            return true;
        } catch (Throwable) { // the reason goes nowhere: a monitor needs to know that, not why
            return false;
        }
    }

    private static function since(?DateTimeImmutable $time, DateTimeImmutable $now, string $late): bool
    {
        return $time !== null && $time > $now->modify("-$late");
    }
}
