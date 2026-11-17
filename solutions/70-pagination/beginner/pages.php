<?php

declare(strict_types=1);

/**
 * How many pages $total items fill, $perPage on each. Nothing found is still one page, an empty one.
 */
function pageCount(int $total, int $perPage): int
{
    return max(1, (int) ceil($total / $perPage));
}

/**
 * How many items to skip for page $page (counted from 1): what goes into SQL's OFFSET.
 */
function offset(int $page, int $perPage): int
{
    return ($page - 1) * $perPage;
}
