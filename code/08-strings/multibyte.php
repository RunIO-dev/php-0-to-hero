<?php

$german = 'Günter Grass';
$arabic = 'نجيب محفوظ';

echo strlen($german), ' bytes, ', mb_strlen($german), " characters\n";
echo strlen($arabic), ' bytes, ', mb_strlen($arabic), " characters\n";

echo strtoupper($german), "\n";
echo mb_strtoupper($german), "\n";

echo mb_substr($arabic, 0, 4), "\n";
