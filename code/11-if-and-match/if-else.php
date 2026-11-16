<?php

$copies = 1;

if ($copies === 0) {
    echo "On loan\n";
} elseif ($copies === 1) {
    echo "Last copy\n";
} else {
    echo "Available\n";
}
