<?php

$book = ['title' => 'Kindred', 'year' => 1979];

echo $book['isbn'] ?? 'no ISBN', "\n";
var_dump(array_key_exists('year', $book));
var_dump(isset($book['isbn']));
