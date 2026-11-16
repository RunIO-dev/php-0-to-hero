<?php

function bookLine($book)
{
    return "{$book['title']} by {$book['author']} ({$book['year']})";
}

$first = ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958];
$second = ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979];
$third = ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988];

echo bookLine($first), "\n";
echo bookLine($second), "\n";
echo bookLine($third), "\n";
