<?php

// Exercise 7, intermediate: the right types.
//
// Someone typed this book in, and put every value in quotes. The title is
// text, so it is right. Fix the other four so that each has the right type:
// the year a whole number, the rating a number with a decimal point,
// $isAvailable yes or no, and $returnedAt no value at all.
// Change only the values; the var_dump lines stay as they are.
//
// Check your work: composer exercise 07 intermediate

$title = 'The Remains of the Day';
$year = '1989';
$rating = '4.25';
$isAvailable = 'true';
$returnedAt = 'null';

var_dump($title);
var_dump($year);
var_dump($rating);
var_dump($isAvailable);
var_dump($returnedAt);
