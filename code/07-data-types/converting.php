<?php

$pagesText = '264';
$pages = (int) $pagesText;
var_dump($pages);

var_dump((string) 1979);
var_dump((int) 3.9);       // cuts off the decimals, does not round
var_dump((bool) 0);
var_dump((bool) 'false');  // any text except '' and '0' is true
