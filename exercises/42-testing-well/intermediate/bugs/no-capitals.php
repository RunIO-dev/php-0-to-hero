<?php

declare(strict_types=1);

/** Forgets the capitals. */
function shelfCode(string $author, int $year): string
{
    $words = explode(' ', trim($author));
    $last = end($words);

    return mb_substr($last, 0, 3) . '-' . $year;
}
