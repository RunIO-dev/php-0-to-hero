<?php

$titles = ['kitchen', 'Sula', 'beloved', 'Kindred'];

sort($titles);
echo implode(', ', $titles), "\n";

sort($titles, SORT_STRING | SORT_FLAG_CASE);
echo implode(', ', $titles), "\n";
