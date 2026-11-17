<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;

/** Where the API's tokens are kept (chapter 69): only their hashes, each for one member, until a date. */
interface TokenRepository
{
    /** Throws MemberNotFound if there is no member with that name. */
    public function add(string $hash, string $member, DateTimeImmutable $expires): void;

    /** The name of the member whose token has this hash, or null if there is none, or it has expired by $now. */
    public function memberName(string $hash, DateTimeImmutable $now): ?string;

    public function remove(string $hash): void;
}
