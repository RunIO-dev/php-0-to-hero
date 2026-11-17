<?php

declare(strict_types=1);

/** A new member. A name is required, and members are at least 6 years old. */
function newMember(string $name, int $age): string
{
    $name = trim($name);
    if ($name === '') {
        throw new InvalidArgumentException('A member needs a name');
    }
    if ($age < 6) {
        throw new InvalidArgumentException("$name is too young: members are at least 6 years old");
    }

    return "Welcome, $name!";
}
