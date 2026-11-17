<?php

declare(strict_types=1);

// Version numbers are not text: compare them with version_compare.

$versions = ['1.9.0', '1.10.0', '1.2.3'];

echo 'As text, the newest is ', max($versions), "\n";

usort($versions, fn(string $a, string $b): int => version_compare($a, $b));
echo 'As versions, the newest is ', end($versions), "\n";
echo 'In order: ', implode(', ', $versions), "\n";
