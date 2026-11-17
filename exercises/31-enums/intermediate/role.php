<?php

declare(strict_types=1);

// Exercise 31, intermediate: a role from typed input.
//
// The enum Role is given. Write a function roleFrom(string $input): Role that
// accepts a role as a person types it: with spaces around it, and in any case
// ("  Staff "). Anything else is refused with an InvalidArgumentException
// whose message lists the roles, taken from the enum itself, so that a new
// case appears in it without changing the function:
//   Unknown role "admin". Use one of: reader, staff, guest

enum Role: string
{
    case Reader = 'reader';
    case Staff = 'staff';
    case Guest = 'guest';
}

// Check your work: composer exercise 31 intermediate
