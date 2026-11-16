<?php

declare(strict_types=1);

if ($argc !== 2) {
    fwrite(STDERR, "Usage: php greet.php <name>\n");
    exit(1);
}

echo "Welcome to the library, $argv[1]!\n";
