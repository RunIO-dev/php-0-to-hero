<?php

declare(strict_types=1);

// Exercise 55, intermediate: the members table (see ../library.php), through one class.
//
// add($name, $role):  adds a member and returns the new id.
// find($id):          ['name' => ..., 'role' => ...], or null if there is no member with that id.
// changeRole($id, $role): stores the new role. No member with that id: OutOfBoundsException.
// remove($id):        removes the member. No member with that id: OutOfBoundsException.
//
// Every value goes through a placeholder.

final readonly class Members
{
    public function __construct(private PDO $db) {}

    public function add(string $name, string $role): int
    {
        return 0;
    }

    /** @return array{name: string, role: string}|null */
    public function find(int $id): ?array
    {
        return null;
    }

    public function changeRole(int $id, string $role): void {}

    public function remove(int $id): void {}
}
// Check your work: composer exercise 55 intermediate
