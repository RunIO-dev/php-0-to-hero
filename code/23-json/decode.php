<?php

declare(strict_types=1);

$json = '{"title": "Kindred", "year": 1979, "tags": ["time travel", "history"]}';

// true: JSON objects become maps. JSON_THROW_ON_ERROR: broken JSON throws.
$book = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

echo $book['title'], ' (', $book['year'], ")\n";
echo implode(', ', $book['tags']), "\n";
var_dump($book['year']);
