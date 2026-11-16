<?php

$german = 'Günter Grass';
$arabic = 'نجيب محفوظ';

echo "$german: ", mb_strlen($german), ' characters, starts with ', mb_substr($german, 0, 1), "\n";
echo "$arabic: ", mb_strlen($arabic), ' characters, starts with ', mb_substr($arabic, 0, 1), "\n";
