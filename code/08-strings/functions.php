<?php

$title = '  The Left Hand of Darkness ';
$clean = trim($title);

echo "[$clean]\n";
echo strlen($clean), "\n";
echo strtoupper($clean), "\n";
echo strtolower($clean), "\n";
echo str_replace('Darkness', 'Light', $clean), "\n";
echo substr($clean, 0, 8), "\n";

var_dump(str_contains($clean, 'Hand'));
var_dump(str_starts_with($clean, 'A '));
