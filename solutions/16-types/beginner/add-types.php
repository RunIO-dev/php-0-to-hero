<?php

declare(strict_types=1);

function pagesLeft(int $total, int $read): int
{
    return $total - $read;
}

function isFinished(int $total, int $read): bool
{
    return pagesLeft($total, $read) <= 0;
}
