<?php

declare(strict_types=1);

// Exercise 40, advanced: mixed data, checked at the edge. A solution.

final readonly class Member
{
    public function __construct(public string $name, public int $card) {}
}

/** @return list<Member> */
function membersFrom(string $json): array
{
    $rows = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($rows) || !array_is_list($rows)) {
        throw new UnexpectedValueException('The members must be a list');
    }
    $members = [];
    foreach ($rows as $row) {
        if (!is_array($row) || !is_string($row['name'] ?? null) || !is_int($row['card'] ?? null)) {
            throw new UnexpectedValueException('Each member needs a name and a card number: ' . json_encode($row));
        }
        $members[] = new Member($row['name'], $row['card']);
    }

    return $members;
}
