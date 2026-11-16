<?php

$titles = ['Kindred', 'Beloved', 'Kitchen'];
$wanted = 'Kindred';

$position = array_search($wanted, $titles, true);

if ($position === false) {
    echo "$wanted is not in the list\n";
} else {
    echo "$wanted is number ", $position + 1, " in the list\n";
}
