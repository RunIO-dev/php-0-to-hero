<?php

// Exercise 10, intermediate: is it the same book?
//
// Two ISBNs and two shelf codes are compared below. They are all different,
// yet both lines print bool(true). Find out why, and change only the
// comparisons, so that both print bool(false).
//
// Check your work: composer exercise 10 intermediate

$typedIsbn = '0451526341';
$storedIsbn = '451526341';
var_dump($typedIsbn == $storedIsbn);

$typedCode = '10';
$storedCode = '1e1';
var_dump($typedCode == $storedCode);
