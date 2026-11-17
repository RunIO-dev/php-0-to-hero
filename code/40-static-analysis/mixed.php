<?php

declare(strict_types=1);

// A file can hold anything, so json_decode can return anything: PHPStan calls that mixed.

$book = json_decode((string) file_get_contents(__DIR__ . '/book.json'), true, flags: JSON_THROW_ON_ERROR);

echo $book['title'], ' is from ', $book['year'], "\n";
