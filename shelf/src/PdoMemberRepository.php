<?php

declare(strict_types=1);

namespace Shelf;

use PDO;

/** The members in the database's members table (chapter 56). */
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
}
