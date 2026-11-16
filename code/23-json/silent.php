<?php

declare(strict_types=1);

// PHP 8.6+ names the place of the error; PHP 8.5 says only "Syntax error".

$json = '{"title": "Kindred", "year": 1979,}';

// Without JSON_THROW_ON_ERROR, broken JSON gives null, and no message at all.
$book = json_decode($json, true);
var_dump($book);
echo json_last_error_msg(), "\n";
