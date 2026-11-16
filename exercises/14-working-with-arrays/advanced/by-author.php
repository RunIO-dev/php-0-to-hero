<?php

// Exercise 14, advanced: all books by one author.
//
// Print the titles of every book by $author, sorted alphabetically and
// separated by a comma and a space. For Toni Morrison:
//   Toni Morrison: Beloved, Song of Solomon, Sula
// When the shelf has no book by the author, print:
//   Toni Morrison: none
// Filter first, then sort, then join. The tests try other authors.
//
// Check your work: composer exercise 14 advanced

$books = [
    ['title' => 'Sula', 'author' => 'Toni Morrison'],
    ['title' => 'Kindred', 'author' => 'Octavia E. Butler'],
    ['title' => 'Song of Solomon', 'author' => 'Toni Morrison'],
    ['title' => 'Beloved', 'author' => 'Toni Morrison'],
];
$author = 'Toni Morrison';
