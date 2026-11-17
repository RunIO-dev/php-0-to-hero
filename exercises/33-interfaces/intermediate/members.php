<?php

declare(strict_types=1);

// Exercise 33, intermediate: members in memory.
//
// Member and the interface MemberRepository are given. Write a class
// InMemoryMemberRepository that implements it, with the members passed to its
// constructor:
// - all() returns them, in order;
// - find(string $name) returns the member with that name, in any case and
//   with spaces around it, or null when there is none.

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

// Check your work: composer exercise 33 intermediate
