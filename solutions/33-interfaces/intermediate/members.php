<?php

declare(strict_types=1);

final readonly class Member
{
    public function __construct(public string $name) {}
}

interface MemberRepository
{
    /** @return list<Member> */
    public function all(): array;

    public function find(string $name): ?Member;
}

final readonly class InMemoryMemberRepository implements MemberRepository
{
    /** @param list<Member> $members */
    public function __construct(private array $members) {}

    public function all(): array
    {
        return $this->members;
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
}
