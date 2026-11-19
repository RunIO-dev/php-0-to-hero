<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The worker's sign of life (chapter 88): every few minutes the schedule sends Message\Heartbeat,
 * and the worker writes the time into a file. The health check reads it: a worker that has stopped
 * sends no reminders, and nothing else would notice. A file in SHELF_DATA, because the app and
 * the worker may be two containers, which share only that folder (compose.prod.yaml).
 */
final readonly class Heartbeat
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/%env(SHELF_DATA)%/worker.alive')]
        private string $file,
    ) {}

    public function beat(DateTimeImmutable $now): void
    {
        if (file_put_contents($this->file, $now->format(DATE_ATOM)) === false) {
            throw new RuntimeException("Cannot write $this->file");
        }
    }

    /** When the worker last said it was alive, or null if it never did. */
    public function last(): ?DateTimeImmutable
    {
        $time = is_file($this->file) ? file_get_contents($this->file) : false;

        return $time === false ? null : (DateTimeImmutable::createFromFormat(DATE_ATOM, $time) ?: null);
    }
}
