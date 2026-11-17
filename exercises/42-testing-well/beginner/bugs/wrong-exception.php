<?php

declare(strict_types=1);

/** Too young is a RuntimeException, which callers don't catch as bad input. */
function newMember(string $name, int $age): string
{
    $name = trim($name);
    if ($name === '') {
        throw new InvalidArgumentException('A member needs a name');
    }
    if ($age < 6) {
        throw new RuntimeException("$name is too young: members are at least 6 years old");
    }

    return "Welcome, $name!";
}
