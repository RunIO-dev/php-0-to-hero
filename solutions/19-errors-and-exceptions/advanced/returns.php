<?php

declare(strict_types=1);

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
    try {
        $loans = giveBack($loans, $title);
        echo "Returned: $title\n";
    } catch (NotOnLoan $e) {
        echo 'Not returned: ', $e->getMessage(), "\n";
    }
}

echo 'Still on loan: ', implode(', ', $loans), "\n";
