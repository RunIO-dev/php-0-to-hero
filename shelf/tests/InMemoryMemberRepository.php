<?php

declare(strict_types=1);

namespace Shelf\Tests;

use Shelf\Member;
use Shelf\MemberRepository;

/** Members for tests, in an array (chapter 56). */
final class InMemoryMemberRepository implements MemberRepository
{
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
}
