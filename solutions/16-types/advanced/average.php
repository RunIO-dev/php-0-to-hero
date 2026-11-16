<?php

declare(strict_types=1);

/** @param list<int|float> $ratings */
function average(array $ratings): float
{
    if ($ratings === []) {
        return 0.0;
    }

    return array_sum($ratings) / count($ratings);
}
