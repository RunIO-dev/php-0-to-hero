<?php

declare(strict_types=1);

// A backed enum: each case has a string (or int) for files and forms.
enum Role: string
{
    case Reader = 'reader';
    case Staff = 'staff';
}

echo Role::Staff->value, "\n";

$role = Role::from('reader');
echo $role->name, "\n";

var_dump(Role::tryFrom('admin'));
