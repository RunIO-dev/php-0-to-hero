<?php

declare(strict_types=1);

// Exercise 44, beginner: a status code.
//
// /book.php?title=sula shows the book, in plain text, with 200 OK.
// For a title that isn't in BOOKS, or no title at all, answer 404 Not Found,
// with the text "No such book" and a new line. Try it: php -S localhost:8000

const BOOKS = ['kindred' => 'Kindred, Octavia E. Butler, 1979', 'sula' => 'Sula, Toni Morrison, 1973'];

header('Content-Type: text/plain; charset=UTF-8');

$key = strtolower($_GET['title'] ?? '');
echo BOOKS[$key] ?? 'No such book', "\n";

// Check your work: composer exercise 44 beginner
