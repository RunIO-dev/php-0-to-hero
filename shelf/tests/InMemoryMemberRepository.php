<?php

declare(strict_types=1);

namespace Shelf\Tests;

use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\MemberRepository;

/** Members for tests, in an array (chapter 56), with their password hashes (61). */
final class InMemoryMemberRepository implements MemberRepository
{
    /** @var array<string, string> lower-case name => password hash */
    private array $hashes = [];

    /** @var list<Member> */
    private array $members = [];

    /** @param list<Member> $members */
    public function __construct(array $members = [])
    {
        array_walk($members, $this->add(...));
    }

    public function find(string $name): ?Member
    {
        foreach ($this->members as $member) {
            if (mb_strtolower($member->name) === mb_strtolower(trim($name))) {
                return $member;
            }
        }

        return null;
    }

    public function add(Member $member): void
    {
        $this->members[] = $member;
        new \ReflectionProperty(Member::class, 'id')->setValue($member, count($this->members)); // as the database would
    }

    public function passwordHash(string $name): ?string
    {
        return $this->hashes[mb_strtolower(trim($name))] ?? null;
    }

    public function changePasswordHash(string $name, string $hash): void
    {
        if ($this->find($name) === null) {
            throw new MemberNotFound('There is no member named "' . trim($name) . '"');
        }
        $this->hashes[mb_strtolower(trim($name))] = $hash;
    }

    public function all(): array
    {
        $all = $this->members;
        usort($all, fn(Member $a, Member $b): int => strcasecmp($a->name, $b->name));

        return $all;
    }

    public function findByEmail(string $email): ?Member
    {
        foreach ($this->members as $member) {
            if ($member->email !== null && $member->email === Member::normalEmail($email)) {
                return $member;
            }
        }

        return null;
    }

    public function findById(int $id): ?Member
    {
        foreach ($this->members as $member) {
            if ($member->id() === $id) {
                return $member;
            }
        }

        return null;
    }

    public function update(Member $member): void {}

    public function remove(Member $member): void
    {
        $this->members = array_values(array_filter($this->members, fn(Member $kept): bool => $kept !== $member));
        unset($this->hashes[mb_strtolower($member->name)]);
    }
}
