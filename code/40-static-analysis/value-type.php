<?php

declare(strict_types=1);

// An array, but an array of what?

function longest(array $titles): string
{
    $longest = '';
    foreach ($titles as $title) {
        if (mb_strlen($title) > mb_strlen($longest)) {
            $longest = $title;
        }
    }

    return $longest;
}

echo longest(['Sula', 'Kindred', 'Beloved']), "\n";
