<?php

declare(strict_types=1);

// Exercise 45, beginner: a setting from the environment. A solution.

header('Content-Type: text/plain; charset=UTF-8');

$name = getenv('LIBRARY_NAME');

echo 'Welcome to ', $name === false || $name === '' ? 'Shelf' : $name, "\n";
