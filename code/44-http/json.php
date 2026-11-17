<?php

declare(strict_types=1);

// The same answer for a program instead of a person: JSON, and a Content-Type that says so.

header('Content-Type: application/json');

echo json_encode(['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979], JSON_THROW_ON_ERROR), "\n";
