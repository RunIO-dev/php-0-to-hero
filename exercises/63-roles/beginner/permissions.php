<?php

declare(strict_types=1);

// Exercise 63, beginner: who may do what, in one place.
//
// mayDo($role, $action) says whether someone with this role may do this action. $role is null for
// a visitor who isn't logged in. The rules:
//
// - 'see books':    everyone, visitors too
// - 'borrow':       readers and staff
// - 'add book', 'remove book', 'see all loans': staff only
//
// Anything else is refused, for everyone: an action nobody wrote a rule for is not allowed.

function mayDo(?string $role, string $action): bool
{
    return true;
}
// Check your work: composer exercise 63 beginner
