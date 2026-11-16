<?php

$costCents = 1000;
$people = 0;

// The right side only runs when the left side is true, so it never divides by 0.
$isExpensive = $people !== 0 && $costCents / $people > 500;
var_dump($isExpensive);
