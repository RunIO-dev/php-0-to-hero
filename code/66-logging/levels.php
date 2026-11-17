<?php

declare(strict_types=1);

// PSR-3: one interface for logging, with eight levels. The code that logs needs only LoggerInterface.

use Psr\Log\LoggerInterface;

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lines.php';

function lend(LoggerInterface $log, string $title, string $member): void
{
    $log->info('{member} borrowed {title}', ['member' => $member, 'title' => $title]);
}

$log = new LineLogger(STDOUT, new DateTimeImmutable('2026-03-16 10:00:00 UTC'));

lend($log, 'Kindred', 'Amira');
$log->warning('Failed login for {name}', ['name' => 'Jonas', 'ip' => '203.0.113.7']);
$log->error('The cover of {title} was not saved', ['title' => 'Sula', 'exception' => new RuntimeException('Disk full')]);
