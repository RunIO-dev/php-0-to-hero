<?php

declare(strict_types=1);

/** @return list<string> */
function tags(string $typed): array
{
    $parts = preg_split('/\s*[,;]\s*/', mb_strtolower(trim($typed))) ?: [];

    return array_values(array_unique(array_filter($parts, fn(string $tag): bool => $tag !== '')));
}
