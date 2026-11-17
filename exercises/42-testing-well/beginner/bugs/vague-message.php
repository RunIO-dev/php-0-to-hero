<?php

declare(strict_types=1);

/** The message doesn't say why. */
function newMember(string $name, int $age): string
{
    $name = trim($name);
    if ($name === '') {
        throw new InvalidArgumentException('A member needs a name');
    }
    if ($age < 6) {
        throw new InvalidArgumentException('Not allowed');
    }

    return "Welcome, $name!";
}
