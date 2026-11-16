<?php

// Exercise 15, advanced: the rule of three.
//
// The same line is written three times below, with only the book changing.
// Write a function bookLine($book) that returns one line, such as
//   Kindred by Octavia E. Butler (1979)
// and use it for all three books. The output must stay exactly the same,
// and ' by ' should appear only once in your file.
//
// Check your work: composer exercise 15 advanced

$first = ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958];
$second = ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979];
$third = ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988];

echo $first['title'] . ' by ' . $first['author'] . ' (' . $first['year'] . ")\n";
echo $second['title'] . ' by ' . $second['author'] . ' (' . $second['year'] . ")\n";
echo $third['title'] . ' by ' . $third['author'] . ' (' . $third['year'] . ")\n";
