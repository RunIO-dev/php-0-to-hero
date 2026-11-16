<?php

$copiesOnShelf = 3;

if ($copiesOnShelf === 0) {
    echo "On loan\n";
} elseif ($copiesOnShelf === 1) {
    echo "Last copy\n";
} else {
    echo "Available\n";
}
