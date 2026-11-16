<?php

$book = ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'copies' => 2];

$book['copies']++;

echo "{$book['title']} by {$book['author']}: {$book['copies']} copies\n";
