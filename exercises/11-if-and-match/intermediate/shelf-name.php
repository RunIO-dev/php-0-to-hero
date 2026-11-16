<?php

// Exercise 11, intermediate: from if to match.
//
// This code works, but the same comparison is repeated on every line. Rewrite
// it with one match expression that stores the result in $shelfName, then
// print it. Any genre not listed goes on the shelf 'General'.
//
// Check your work: composer exercise 11 intermediate

$genre = 'crime';

if ($genre === 'sf') {
    $shelfName = 'Science fiction';
} elseif ($genre === 'crime') {
    $shelfName = 'Crime';
} elseif ($genre === 'poetry') {
    $shelfName = 'Poetry';
} elseif ($genre === 'kids') {
    $shelfName = 'Young readers';
} elseif ($genre === 'teens') {
    $shelfName = 'Young readers';
} else {
    $shelfName = 'General';
}

echo $shelfName, "\n";
