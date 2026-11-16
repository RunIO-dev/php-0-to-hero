<?php

declare(strict_types=1);

// Exercise 19, advanced: one bad return must not stop the desk.
//
// Members bring back the books in $returns. One of them was never lent, and
// right now the program stops there. Catch NotOnLoan for each book, so that
// every return is handled and the program prints:
//   Returned: Kindred
//   Not returned: Sula is not on loan
//   Returned: Kitchen
//   Still on loan: Beloved
// Catch only NotOnLoan, nothing wider.
//
// Check your work: composer exercise 19 advanced

final class NotOnLoan extends RuntimeException {}

/**
 * The loans after $title is given back.
 *
 * @param list<string> $loans
 * @return list<string>
 */
function giveBack(array $loans, string $title): array
{
    $position = array_search($title, $loans, true);
    if ($position === false) {
        throw new NotOnLoan("$title is not on loan");
    }
    unset($loans[$position]);

    return array_values($loans);
}

$loans = ['Kindred', 'Kitchen', 'Beloved'];
$returns = ['Kindred', 'Sula', 'Kitchen'];

foreach ($returns as $title) {
    $loans = giveBack($loans, $title);
    echo "Returned: $title\n";
}

echo 'Still on loan: ', implode(', ', $loans), "\n";
