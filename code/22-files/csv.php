<?php

declare(strict_types=1);

$file = fopen(__DIR__ . '/books.csv', 'r');
$header = fgetcsv($file, escape: '');

// fgetcsv reads one line and splits it into fields; at the end, it returns false.
while (($row = fgetcsv($file, escape: '')) !== false) {
    $book = array_combine($header, $row);
    echo "{$book['title']} ({$book['year']}): {$book['copies']} copies\n";
}

fclose($file);
