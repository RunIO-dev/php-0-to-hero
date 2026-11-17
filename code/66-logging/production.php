<?php

declare(strict_types=1);

// In production: the visitor gets a short message, and the log gets the details.

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lines.php';

ini_set('display_errors', '0'); // never show PHP's own error messages to a visitor
$log = new LineLogger(STDOUT, new DateTimeImmutable('2026-03-16 10:00:00 UTC'));

set_exception_handler(static function (Throwable $e) use ($log): void {
    $reference = 'c0ffee42'; // in real code: bin2hex(random_bytes(4)), new each time
    $log->error('Uncaught {class}, reference {reference}', ['class' => $e::class, 'reference' => $reference, 'exception' => $e]);
    http_response_code(500);
    echo "Something went wrong. It has been noted, reference $reference.\n";
});

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->query('SELECT * FROM books'); // there is no books table
