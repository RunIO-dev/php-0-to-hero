<?php

declare(strict_types=1);

// Is it working (chapter 88)? Each check is a question with a yes or a no. A check that throws is a
// no, and its reason goes into the log, never into the answer: anyone may ask.

$now = new DateTimeImmutable('2026-03-16 10:00');
/** @var array<string, Closure(): bool> $checks */
$checks = [
    'database' => fn(): bool => new PDO('sqlite::memory:')->query('SELECT 1') !== false,
    'worker' => fn(): bool => new DateTimeImmutable('2026-03-16 09:55') > $now->modify('-15 minutes'),
    'backup' => fn(): bool => throw new RuntimeException('/srv/shelf/backups: Permission denied'),
];

$answers = [];
$log = [];
foreach ($checks as $name => $check) {
    try {
        $answers[$name] = $check() ? 'ok' : 'failing';
    } catch (Throwable $e) {
        $answers[$name] = 'failing';
        $log[] = "health check $name: {$e->getMessage()}";
    }
}
$ok = !in_array('failing', $answers, true);

echo 'HTTP ', $ok ? 200 : 503, "\n";
echo json_encode(['status' => $ok ? 'ok' : 'failing', 'checks' => $answers], JSON_PRETTY_PRINT), "\n";
echo 'The log: ', implode("\n", $log), "\n";
