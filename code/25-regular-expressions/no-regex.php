<?php

declare(strict_types=1);

$title = 'The Left Hand of Darkness';

// For fixed text, string functions are shorter and clearer than a regex.
var_dump(str_starts_with($title, 'The '));
var_dump(str_contains($title, 'Hand'));
var_dump(ctype_digit('1979'));
