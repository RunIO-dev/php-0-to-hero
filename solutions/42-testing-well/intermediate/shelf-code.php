<?php

declare(strict_types=1);

/**
 * The code on a book's spine: the first three letters of the author's last name in capitals,
 * and the year: "Octavia E. Butler", 1979 => "BUT-1979".
 */
function shelfCode(string $author, int $year): string
{
    $words = explode(' ', trim($author));
    $last = end($words);

    return mb_strtoupper(mb_substr($last, 0, 3)) . '-' . $year;
}
