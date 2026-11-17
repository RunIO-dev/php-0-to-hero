<?php

declare(strict_types=1);

// Exercise 63, beginner: who may do what, in one place.

const RULES = [
    'see books' => [null, 'reader', 'staff'],
    'borrow' => ['reader', 'staff'],
    'add book' => ['staff'],
    'remove book' => ['staff'],
    'see all loans' => ['staff'],
];

function mayDo(?string $role, string $action): bool
{
    return in_array($role, RULES[$action] ?? [], true);
}
