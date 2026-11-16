<?php

// Exercise 8, advanced: names in any language.
//
// This script should print, for each author, the number of characters in
// the name and its first letter:
//   Günter Grass: 12 characters, starts with G
//   نجيب محفوظ: 10 characters, starts with ن
// It counts and cuts wrongly. Find out why, and fix it.
//
// Check your work: composer exercise 08 advanced

$german = 'Günter Grass';
$arabic = 'نجيب محفوظ';

echo "$german: ", strlen($german), ' characters, starts with ', substr($german, 0, 1), "\n";
echo "$arabic: ", strlen($arabic), ' characters, starts with ', substr($arabic, 0, 1), "\n";
