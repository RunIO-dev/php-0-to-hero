<?php

$titles = ['Kindred', 'Beloved', 'Kitchen'];
$position = array_search('Kindred', $titles, true);

if ($position) {
    echo "Found at $position\n";
} else {
    echo "Not found\n";
}

if ($position !== false) {
    echo "Found at $position\n";
}
