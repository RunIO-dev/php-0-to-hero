<?php

declare(strict_types=1);

// Every month from $from to $to (chapter 86), both "Y-m", with its count from $counts, or 0 where the
// database had no row: ['2026-01' => 2, '2026-02' => 0, ...]. An InvalidArgumentException if $from
// comes after $to. Careful: "2026-01-31 +1 month" is 3 March. Count from the first day of a month.

/**
 * @param array<string, int> $counts
 * @return array<string, int>
 */
function everyMonth(array $counts, string $from, string $to): array
{
    return []; // TODO
}
