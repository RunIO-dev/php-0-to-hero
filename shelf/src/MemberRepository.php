<?php

declare(strict_types=1);

namespace Shelf;

/** Where the members are kept (chapter 56). A member is found by name, in any case. */
interface MemberRepository
{
    public function find(string $name): ?Member;

    public function add(Member $member): void;
}
