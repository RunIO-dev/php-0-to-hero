<?php

declare(strict_types=1);

/**
 * The longest title in a list.
 *
 * @param list<string> $titles
 */
function longestTitle(array $titles): string
{
    $longest = '';
    foreach ($titles as $title) {
        if (mb_strlen($title) > mb_strlen($longest)) {
            $longest = $title;
        }
    }

    return $longest;
}

echo longestTitle(['Kindred', 'Things Fall Apart', 'Kitchen']), "\n";
