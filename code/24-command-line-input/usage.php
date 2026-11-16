<?php

declare(strict_types=1);

if ($argc !== 2) {
    // Messages for people go to STDERR, and exit(1) tells the shell that it failed.
    fwrite(STDERR, "Usage: php usage.php <title>\n");
    exit(1);
}

echo "Looking for \"$argv[1]\"\n";
