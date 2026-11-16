<?php

declare(strict_types=1);

$books = [
    ['title' => 'Kindred', 'copies' => 0],
    ['title' => 'Kitchen', 'copies' => 1],
];
$path = sys_get_temp_dir() . '/shelf-books.json';

// Save: the list becomes a JSON array of objects.
$json = json_encode($books, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
file_put_contents($path, $json . "\n");
echo file_get_contents($path);

// Load: and back into a list of maps.
$loaded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
var_dump($loaded === $books);

unlink($path);
