<?php

declare(strict_types=1);

/**
 * @param list<string> $titles
 * @return list<string>
 */
function labels(array $titles): array
{
    return array_map(fn(string $title): string => '[' . mb_strtoupper($title) . ']', $titles);
}
