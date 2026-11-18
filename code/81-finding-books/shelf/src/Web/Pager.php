<?php

declare(strict_types=1);

namespace Shelf\Web;

use InvalidArgumentException;

/**
 * Which page numbers a list shows under it (chapter 81): the first and the last, the current one and
 * its neighbours, and a gap (null) for the numbers in between. 1 … 4 5 6 … 20, never twenty links.
 */
final class Pager
{
    /** @return list<int|null> the numbers in order, null where some are left out */
    public static function numbers(int $current, int $pages): array
    {
        if ($pages < 1 || $current < 1 || $current > $pages) {
            throw new InvalidArgumentException("Page $current of $pages doesn't exist");
        }
        $shown = array_unique([1, $current - 1, $current, $current + 1, $pages]);
        $shown = array_filter($shown, fn(int $page): bool => $page >= 1 && $page <= $pages);
        sort($shown);

        $numbers = [];
        $previous = 0;
        foreach ($shown as $page) {
            if ($page - $previous === 2) {
                $numbers[] = $page - 1; // a gap of one number: the number takes no more room than "…"
            } elseif ($page - $previous > 2) {
                $numbers[] = null;
            }
            $numbers[] = $page;
            $previous = $page;
        }

        return $numbers;
    }
}
