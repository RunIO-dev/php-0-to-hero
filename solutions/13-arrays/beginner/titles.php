<?php

$titles = ['Kindred', 'Kitchen', 'Sula'];

$titles[] = 'Beloved';

echo count($titles), " titles\n";
echo "First: {$titles[0]}\n";
echo 'Last: ', $titles[count($titles) - 1], "\n";
