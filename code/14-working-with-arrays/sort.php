<?php

$years = [1988, 1958, 1979];
sort($years);
echo implode(', ', $years), "\n";

rsort($years);
echo implode(', ', $years), "\n";

$titles = ['Sula', 'Kindred', 'Beloved'];
sort($titles);
echo implode(', ', $titles), "\n";
