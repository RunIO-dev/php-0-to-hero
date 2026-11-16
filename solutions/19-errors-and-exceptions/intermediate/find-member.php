<?php

declare(strict_types=1);

final class MemberNotFound extends RuntimeException {}

/**
 * @param list<array{name: string, email: string}> $members
 * @return array{name: string, email: string}
 */
function findMember(array $members, string $email): array
{
    foreach ($members as $member) {
        if (mb_strtolower($member['email']) === mb_strtolower($email)) {
            return $member;
        }
    }

    throw new MemberNotFound("No member with the email $email");
}
