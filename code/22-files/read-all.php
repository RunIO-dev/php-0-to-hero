<?php

declare(strict_types=1);

// __DIR__ is the folder of this file, so the path works from any folder.
$text = file_get_contents(__DIR__ . '/titles.txt');

echo $text;
echo strlen($text), " bytes\n";
