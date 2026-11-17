<?php

declare(strict_types=1);

// A name typed into the login form, with a line break and a fake log line in it.

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lines.php';

$log = new LineLogger(STDOUT, new DateTimeImmutable('2026-03-16 10:00:00 UTC'));

$name = "Amira\n2026-03-16T10:00:01+00:00 INFO Jonas logged in";
$log->warning('Failed login for {name}', ['name' => $name]);
