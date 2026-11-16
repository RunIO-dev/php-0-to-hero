<?php

declare(strict_types=1);

// Exercise 26, beginner: one function, two scripts.
//
// fees.php and receipt.php both define money(). Move it into a new file
// money.php in this folder, and load it in both scripts with
//   require_once __DIR__ . '/money.php';
// Both scripts must print the same as before.
//
// Check your work: composer exercise 26 beginner

function money(int $cents): string
{
    return number_format($cents / 100, 2) . ' EUR';
}

echo 'Late fees today: ', money(350), "\n";
