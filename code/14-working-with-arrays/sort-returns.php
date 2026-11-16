<?php

$titles = ['Sula', 'Kindred', 'Beloved'];

$sorted = sort($titles);
var_dump($sorted);
echo implode(', ', $titles), "\n";
