<?php

declare(strict_types=1);

// Exercise 55, intermediate: the members table (see ../library.php), through one class.

final readonly class Members
{
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

    public function changeRole(int $id, string $role): void
    {
        $change = $this->db->prepare('UPDATE members SET role = :role WHERE id = :id');
        $change->execute(['role' => $role, 'id' => $id]);
        if ($change->rowCount() === 0) {
            throw new OutOfBoundsException("There is no member $id");
        }
    }

    public function remove(int $id): void
    {
        $remove = $this->db->prepare('DELETE FROM members WHERE id = :id');
        $remove->execute(['id' => $id]);
        if ($remove->rowCount() === 0) {
            throw new OutOfBoundsException("There is no member $id");
        }
    }
}
