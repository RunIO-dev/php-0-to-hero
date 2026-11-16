<?php

declare(strict_types=1);

echo 'Your name: ';
// fgets reads one line that the user types, with the Enter at its end.
$name = trim((string) fgets(STDIN));

echo "Welcome, $name!\n";
