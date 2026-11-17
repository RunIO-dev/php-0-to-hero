<?php

declare(strict_types=1);

/** Anyone may join, at any age. */
function newMember(string $name, int $age): string
{
    $name = trim($name);
    if ($name === '') {
        throw new InvalidArgumentException('A member needs a name');
    }

    return "Welcome, $name!";
}
