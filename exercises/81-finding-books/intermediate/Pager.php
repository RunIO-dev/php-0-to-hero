<?php

declare(strict_types=1);

// The page numbers under a list (chapter 81): the first page and the last, the current one with
// $around pages on each side, and null for a gap of left-out numbers, shown as "…".
//   Pager::numbers(10, 20)    === [1, null, 9, 10, 11, null, 20]
//   Pager::numbers(10, 20, 2) === [1, null, 8, 9, 10, 11, 12, null, 20]
// A gap of a single number shows that number instead: it takes no more room than "…".
//   Pager::numbers(3, 20)     === [1, 2, 3, 4, null, 20]
// A page that doesn't exist (0, or after the last) is an InvalidArgumentException.

final class Pager
{
    /** @return list<int|null> */
    public static function numbers(int $current, int $pages, int $around = 1): array
    {
        return range(1, $pages); // TODO
    }
}
