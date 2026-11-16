<?php

$titles = ['Kindred', '', 'Kitchen', 'Beloved'];

$number = 0;
foreach ($titles as $title) {
    if ($title === '') {
        continue;
    }
    $number++;
    echo "$number. $title\n";
}

if ($number === 0) {
    echo "No books yet.\n";
}
