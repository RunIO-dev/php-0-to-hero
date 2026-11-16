<?php

declare(strict_types=1);

// Brackets capture parts; (?<name>...) gives a part a name.
$pattern = '/^(?<title>.+) \((?<year>\d{4})\)$/';

foreach (['Kindred (1979)', 'Things Fall Apart (1958)', 'Kitchen'] as $line) {
    if (preg_match($pattern, $line, $match) === 1) {
        echo "title: {$match['title']}, year: {$match['year']}\n";
    } else {
        echo "no year in \"$line\"\n";
    }
}
