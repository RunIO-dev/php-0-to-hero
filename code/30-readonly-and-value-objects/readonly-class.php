<?php

declare(strict_types=1);

// readonly before class makes every property readonly.
final readonly class Member
{
    public function __construct(
        public string $name,
        public DateTimeImmutable $since,
    ) {}
}

$member = new Member('Amira', new DateTimeImmutable('2024-03-01'));

echo "$member->name, a member since {$member->since->format('F Y')}\n";
