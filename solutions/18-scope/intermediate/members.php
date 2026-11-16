<?php

declare(strict_types=1);

/**
 * @param list<string> $members
 * @return list<string>
 */
function withMember(array $members, string $name): array
{
    if (in_array($name, $members, true)) {
        return $members;
    }
    $members[] = $name;

    return $members;
}
