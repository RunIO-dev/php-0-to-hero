<?php

declare(strict_types=1);

// Exercise 40, advanced: mixed data, checked at the edge.
//
// membersFrom() turns JSON such as [{"name": "Amira", "card": 1042}] into Member
// objects. PHPStan at level 10 refuses it: json_decode returns mixed.
//   vendor/bin/phpstan analyse --level 10 exercises/40-static-analysis/advanced/members.php
// Check the data, so that level 10 finds nothing. For anything that is not a list
// of objects with a string "name" and an int "card", throw an UnexpectedValueException.

final readonly class Member
{
    public function __construct(public string $name, public int $card) {}
}

/** @return list<Member> */
function membersFrom(string $json): array
{
    $members = [];
    foreach (json_decode($json, true, flags: JSON_THROW_ON_ERROR) as $row) {
        $members[] = new Member($row['name'], $row['card']);
    }

    return $members;
}

// Check your work: composer exercise 40 advanced
