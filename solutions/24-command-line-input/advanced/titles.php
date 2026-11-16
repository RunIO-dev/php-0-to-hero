<?php

declare(strict_types=1);

$titles = [];
while (($line = fgets(STDIN)) !== false) {
    $title = trim($line);
    $key = mb_strtolower($title);
    if ($title !== '' && !isset($titles[$key])) {
        $titles[$key] = $title;
    }
}

if ($titles === []) {
    echo "No titles\n";
    exit(0);
}

ksort($titles, SORT_STRING);
$number = 0;
foreach ($titles as $title) {
    $number++;
    echo "$number. $title\n";
}
echo $number, $number === 1 ? " title\n" : " titles\n";
