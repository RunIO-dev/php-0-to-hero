<?php

declare(strict_types=1);

// Bug: changeRole() says nothing when there is no member with that id.

final readonly class Members
{
    /** The table this class needs. A test runs it in a new database. */
    public const string TABLE = "CREATE TABLE members (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL UNIQUE,
        role TEXT NOT NULL CHECK (role IN ('reader', 'staff'))
    )";

    public function __construct(private PDO $db) {}

    public function add(string $name, string $role): int
    {
        $add = $this->db->prepare('INSERT INTO members (name, role) VALUES (:name, :role)');
        $add->execute(['name' => $name, 'role' => $role]);

        return (int) $this->db->lastInsertId();
    }

    /** @return array{name: string, role: string}|null */
    public function find(int $id): ?array
    {
        $find = $this->db->prepare('SELECT name, role FROM members WHERE id = :id');
        $find->execute(['id' => $id]);
        $row = $find->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row) || !is_string($row['name'] ?? null) || !is_string($row['role'] ?? null)) {
            return null;
        }

        return ['name' => $row['name'], 'role' => $row['role']];
    }

    /** Throws an OutOfBoundsException if there is no member with this id. */
    public function changeRole(int $id, string $role): void
    {
        $change = $this->db->prepare('UPDATE members SET role = :role WHERE id = :id');
        $change->execute(['role' => $role, 'id' => $id]);
    }

    /** Throws an OutOfBoundsException if there is no member with this id. */
    public function remove(int $id): void
    {
        $remove = $this->db->prepare('DELETE FROM members WHERE id = :id');
        $remove->execute(['id' => $id]);
        if ($remove->rowCount() === 0) {
            throw new OutOfBoundsException("There is no member $id");
        }
    }
}
