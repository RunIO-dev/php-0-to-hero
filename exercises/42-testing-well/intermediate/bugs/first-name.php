<?php

declare(strict_types=1);

/** Uses the first name instead of the last. */
function shelfCode(string $author, int $year): string
{
    $words = explode(' ', trim($author));
    $last = $words[0];

    return mb_strtoupper(mb_substr($last, 0, 3)) . '-' . $year;
}
