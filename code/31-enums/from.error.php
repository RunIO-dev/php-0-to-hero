<?php

declare(strict_types=1);

enum Role: string
{
    case Reader = 'reader';
    case Staff = 'staff';
}

$role = Role::from('admin');
