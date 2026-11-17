<?php

declare(strict_types=1);

// ✗ A fast hash without a salt, as passwords were once stored. Never do this: use password_hash.

$stored = [
    'Amira' => hash('sha256', 'sunflower'),
    'Jonas' => hash('sha256', 'sunflower'),
    'Kenji' => hash('sha256', 'kenji1990'),
];
foreach ($stored as $name => $hash) {
    echo "$name: ", substr($hash, 0, 16), "…\n";
}

// An attacker with a copy of the table hashes a list of common passwords, millions per second.
foreach (['123456', 'password', 'qwerty', 'sunflower', 'kenji1990'] as $guess) {
    $found = array_keys($stored, hash('sha256', $guess), true);
    if ($found !== []) {
        echo implode(' and ', $found), count($found) === 1 ? ' uses' : ' use', " \"$guess\"\n";
    }
}
