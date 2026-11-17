<?php

declare(strict_types=1);

// Exercise 36, intermediate: where does a class live?
//
// PSR-4 says how a class name becomes a file path. With the prefix "Shelf\"
// and the folder "/app/src":
//   Shelf\Book            ->  /app/src/Book.php
//   Shelf\Storage\Json    ->  /app/src/Storage/Json.php
//   Other\Book            ->  null: it doesn't start with the prefix
//
// Write a function classFile(string $class, string $prefix, string $dir): ?string
// that does this. The prefix ends with a backslash; the folder has no slash at
// the end. A class name that doesn't start with the prefix gives null.
//
// Check your work: composer exercise 36 intermediate
