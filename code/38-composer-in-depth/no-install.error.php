<?php

declare(strict_types=1);

// A project whose packages were never installed: there is no vendor/ folder yet.
// PHP 8.6+ wording: PHP 8.5 also names the file in the warning, require(vendor/autoload.php).

require __DIR__ . '/vendor/autoload.php';

echo "Welcome to the reading club\n";
