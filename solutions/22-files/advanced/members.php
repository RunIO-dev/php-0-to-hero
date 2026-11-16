<?php

declare(strict_types=1);

/** @return list<array{name: string, email: string}> */
function loadMembers(string $path): array
{
    $file = is_readable($path) ? fopen($path, 'r') : false;
    if ($file === false) {
        throw new RuntimeException("Cannot read the members from $path");
    }
    try {
        $members = [];
        fgetcsv($file, escape: '');
        while (($row = fgetcsv($file, escape: '')) !== false) {
            $members[] = ['name' => (string) $row[0], 'email' => (string) ($row[1] ?? '')];
        }

        return $members;
    } finally {
        fclose($file);
    }
}
