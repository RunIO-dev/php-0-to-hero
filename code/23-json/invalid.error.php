<?php

declare(strict_types=1);

// PHP 8.6+ names the place of the error; PHP 8.5 says only "Syntax error".

// A comma after the last item is not allowed in JSON.
$json = '{"title": "Kindred", "year": 1979,}';

$book = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
echo $book['title'], "\n";
