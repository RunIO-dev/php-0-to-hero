<?php

declare(strict_types=1);

enum Role: string
{
    case Reader = 'reader';
    case Staff = 'staff';
    case Guest = 'guest';
}

function roleFrom(string $input): Role
{
    $role = Role::tryFrom(strtolower(trim($input)));
    if ($role === null) {
        $roles = implode(', ', array_map(fn(Role $role): string => $role->value, Role::cases()));
        throw new InvalidArgumentException("Unknown role \"$input\". Use one of: $roles");
    }

    return $role;
}
