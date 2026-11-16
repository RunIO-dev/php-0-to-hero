<?php

$costCents = 1000;
$people = 3;

$share = intdiv($costCents, $people);
$leftover = $costCents % $people;

echo 'Amira: ', $share + $leftover, "\n";
echo 'Jonas: ', $share, "\n";
echo 'Kenji: ', $share, "\n";
