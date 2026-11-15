<?php

$left = 'Frankenstein';
$right = 'Dracula';

// Keep the left book aside, or it is lost when $left gets the right one.
$aside = $left;
$left = $right;
$right = $aside;

echo 'Left:  ', $left, "\n";
echo 'Right: ', $right, "\n";
