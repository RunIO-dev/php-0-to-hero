<?php

declare(strict_types=1);

namespace Shelf;

/** Where the members are kept (chapter 56). A member is found by name, in any case. */
interface MemberRepository
{
    public function find(string $name): ?Member;

    public function add(Member $member): void;

    /** The member's password hash (chapter 61), or null when they have none, or there is no such member. */
    public function passwordHash(string $name): ?string;

    /** Stores a new password hash. Throws MemberNotFound if there is no member with that name. */
    public function changePasswordHash(string $name, string $hash): void;
}
