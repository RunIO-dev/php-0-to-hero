<?php

declare(strict_types=1);

$json = '{"title": "Kindred", "year": 1979}';

// Without true, a JSON object becomes an object, not a map.
$book = json_decode($json, flags: JSON_THROW_ON_ERROR);
echo $book['title'], "\n";
