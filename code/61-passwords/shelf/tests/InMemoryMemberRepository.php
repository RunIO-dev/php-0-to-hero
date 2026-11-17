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

    /** @param list<Member> $members */
    public function __construct(private array $members = []) {}

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
}
