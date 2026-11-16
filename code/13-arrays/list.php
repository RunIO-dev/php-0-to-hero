<?php

$titles = ['Kindred', 'Beloved', 'Kitchen'];

echo $titles[0], "\n";
echo $titles[2], "\n";
echo count($titles), " titles\n";

$titles[] = 'Things Fall Apart';
$titles[1] = 'Sula';

echo count($titles), " titles\n";
echo $titles[1], "\n";
