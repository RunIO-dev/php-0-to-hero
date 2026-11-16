<?php

declare(strict_types=1);

// Exercise 16, intermediate: text from outside.
//
// The number of days late arrives as text, as everything typed in by a person
// does. Run this file: lateFee() refuses the string. Fix it where the value
// comes in, by turning $input into an int. Leave lateFee() as it is.
//   Fee: 200 cents
//
// Check your work: composer exercise 16 intermediate

function lateFee(int $daysLate): int
{
    return $daysLate * 50;
}

$input = '4';

echo 'Fee: ', lateFee($input), " cents\n";
