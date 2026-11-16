<?php

declare(strict_types=1);

// A kind of exception with its own name. (Classes are explained in Part 5.)
final class BookNotFound extends RuntimeException {}

/** @param list<string> $titles */
function positionOf(array $titles, string $title): int
{
    $position = array_search($title, $titles, true);
    if ($position === false) {
        throw new BookNotFound("No book titled \"$title\"");
    }

    return $position;
}

$titles = ['Things Fall Apart', 'Kindred', 'Kitchen'];

foreach (['Kindred', 'Sula'] as $wanted) {
    try {
        $position = positionOf($titles, $wanted);
        echo "$wanted is at position $position\n";
    } catch (BookNotFound $e) {
        echo $e->getMessage(), ". Try another title.\n";
    }
}
