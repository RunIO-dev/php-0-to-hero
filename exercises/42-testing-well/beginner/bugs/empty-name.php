<?php

declare(strict_types=1);

/** A name of only spaces is accepted. */
function newMember(string $name, int $age): string
{
    if ($name === '') {
        throw new InvalidArgumentException('A member needs a name');
    }
    if ($age < 6) {
        throw new InvalidArgumentException("$name is too young: members are at least 6 years old");
    }

    return "Welcome, $name!";
}
