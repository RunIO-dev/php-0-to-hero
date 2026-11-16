<?php

// Exercise 10, advanced: predict the answers.
//
// Don't run this file yet. Write into prediction.txt exactly what you think
// it prints, one line per var_dump. Then run it and compare.
//
// Check your work: composer exercise 10 advanced

$copies = 0;
$isMember = true;

var_dump($isMember && $copies > 0);
var_dump($isMember || $copies > 0);
var_dump(!$isMember || $copies === 0);
var_dump('10' == '1e1');
var_dump('10' === '1e1');
var_dump('Zebra' < 'apple');
var_dump(2 <=> 10);
