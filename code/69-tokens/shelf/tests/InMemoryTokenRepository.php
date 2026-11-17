<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use Shelf\MemberNotFound;
use Shelf\MemberRepository;
use Shelf\TokenRepository;

/** Tokens for tests, in an array (chapter 69). */
final class InMemoryTokenRepository implements TokenRepository
{
    /** @var array<string, array{member: string, expires: DateTimeImmutable}> hash => member and expiry */
    public array $rows = [];

    public function __construct(private MemberRepository $members) {}

    public function add(string $hash, string $member, DateTimeImmutable $expires): void
    {
        $found = $this->members->find($member) ?? throw new MemberNotFound("There is no member named \"$member\"");
        $this->rows[$hash] = ['member' => $found->name, 'expires' => $expires];
    }

    public function memberName(string $hash, DateTimeImmutable $now): ?string
    {
        $row = $this->rows[$hash] ?? null;

        return $row !== null && $row['expires'] > $now ? $row['member'] : null;
    }

    public function remove(string $hash): void
    {
        unset($this->rows[$hash]);
    }
}
