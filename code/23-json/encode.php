<?php

declare(strict_types=1);

$book = [
    'title' => 'Schöne neue Welt',
    'author' => 'Aldous Huxley',
    'year' => 1932,
    'onLoan' => false,
];

// One line, with ö written as the code \u00f6.
echo json_encode($book, JSON_THROW_ON_ERROR), "\n";

// For people: indented, and with the letters as they are.
$forPeople = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;
echo json_encode($book, $forPeople), "\n";
