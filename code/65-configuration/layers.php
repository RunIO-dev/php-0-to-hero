<?php

declare(strict_types=1);

// Three layers: the file in Git, the file for this computer, and the server's environment.
// A later layer wins, so the server can change any setting without touching a file.

require_once __DIR__ . '/envfile.php';

$server = ['LIBRARY_DATABASE' => '/srv/library/library.sqlite']; // as a server's environment would set it

$settings = [...readEnvFile(__DIR__ . '/library.env'), ...readEnvFile(__DIR__ . '/library.local.env'), ...$server];
foreach ($settings as $name => $value) {
    echo $name, ' = ', str_contains($name, 'PASSWORD') ? '(hidden)' : $value, "\n";
}
