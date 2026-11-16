<?php

// Shelf: a small lending library. Chapter 11: the book shows whether it is available.

$title = 'Things Fall Apart';
$author = 'Chinua Achebe';
$year = 1958;
$copies = 2;

$status = $copies > 0 ? 'available' : 'all copies on loan';

echo "Title:  $title\n";
echo "Author: $author\n";
echo "Year:   $year\n";
echo "Copies: $copies\n";
echo "Status: $status\n";
