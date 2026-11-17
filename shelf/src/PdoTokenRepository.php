<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use PDO;

/** The API's tokens in the database's api_tokens table (chapter 69). */
final readonly class PdoTokenRepository implements TokenRepository
{
    public function __construct(private PDO $db) {}

    public function add(string $hash, string $member, DateTimeImmutable $expires): void
    {
        $add = $this->db->prepare('INSERT INTO api_tokens (hash, member_id, expires)
            SELECT :hash, id, :expires FROM members WHERE name = :member');
        $add->execute(['hash' => $hash, 'expires' => $expires->format(DATE_ATOM), 'member' => $member]);
        if ($add->rowCount() === 0) {
            throw new MemberNotFound("There is no member named \"$member\"");
        }
    }

    public function memberName(string $hash, DateTimeImmutable $now): ?string
    {
        $find = $this->db->prepare('SELECT members.name FROM api_tokens JOIN members ON members.id = api_tokens.member_id
            WHERE api_tokens.hash = :hash AND api_tokens.expires > :now');
        $find->execute(['hash' => $hash, 'now' => $now->format(DATE_ATOM)]);
        $name = $find->fetchColumn();

        return is_string($name) ? $name : null;
    }

    public function remove(string $hash): void
    {
        $this->db->prepare('DELETE FROM api_tokens WHERE hash = :hash')->execute(['hash' => $hash]);
    }
}
