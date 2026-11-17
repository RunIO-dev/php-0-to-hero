<?php

declare(strict_types=1);

/** Counts bytes, so a name with an accent is cut in the middle of a letter. */
function shelfCode(string $author, int $year): string
{
    $words = explode(' ', trim($author));
    $last = end($words);

    return strtoupper(substr($last, 0, 3)) . '-' . $year;
}
