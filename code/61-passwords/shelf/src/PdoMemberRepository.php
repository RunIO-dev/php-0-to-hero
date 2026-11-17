<?php

declare(strict_types=1);

namespace Shelf;

use PDO;

/** The members in the database's members table (chapter 56), with their password hashes (61). */
final readonly class PdoMemberRepository implements MemberRepository
{
    public function __construct(private PDO $db) {}

    public function find(string $name): ?Member
    {
        $find = $this->db->prepare('SELECT name, role FROM members WHERE name = :name');
        $find->execute(['name' => trim($name)]);
        $row = $find->fetch();

        return $row === false ? null : Member::fromArray($row);
    }

    public function add(Member $member): void
    {
        $add = $this->db->prepare('INSERT INTO members (name, role) VALUES (:name, :role)');
        $add->execute(['name' => $member->name, 'role' => $member->role->value]);
    }

    public function passwordHash(string $name): ?string
    {
        $find = $this->db->prepare('SELECT password_hash FROM members WHERE name = :name');
        $find->execute(['name' => trim($name)]);
        $hash = $find->fetchColumn();

        return is_string($hash) ? $hash : null;
    }

    public function changePasswordHash(string $name, string $hash): void
    {
        $change = $this->db->prepare('UPDATE members SET password_hash = :hash WHERE name = :name');
        $change->execute(['hash' => $hash, 'name' => trim($name)]);
        if ($change->rowCount() === 0) {
            throw new MemberNotFound('There is no member named "' . trim($name) . '"');
        }
    }
}
