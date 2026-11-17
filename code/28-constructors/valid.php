<?php

declare(strict_types=1);

final class Member
{
    // The constructor refuses what no member may be, so every Member is valid.
    public function __construct(public string $name)
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('A member needs a name');
        }
    }
}

$amira = new Member('Amira');
echo "Welcome, $amira->name\n";

try {
    $nobody = new Member('  ');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(), "\n";
}
