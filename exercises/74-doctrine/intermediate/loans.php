<?php

declare(strict_types=1);

// Questions the library's desk asks, in DQL, about the entities in entities/. Each value from outside
// goes in as a parameter, never into the query's text.

use Desk\Loan;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The loans that were due before $today, the longest overdue first, each with its book in the same query.
 *
 * @return list<Loan>
 */
function overdue(EntityManagerInterface $entities, DateTimeImmutable $today): array
{
    // TODO: only the overdue ones, the longest overdue first
    return array_values($entities->getRepository(Loan::class)->findAll());
}

/**
 * The titles a member has on loan, the soonest due first.
 *
 * @return list<string>
 */
function titlesOf(EntityManagerInterface $entities, string $member): array
{
    return []; // TODO
}

/**
 * How many loans each book has, the most first, then by title: title => count. Books on no loan are left out.
 *
 * @return array<string, int>
 */
function busiest(EntityManagerInterface $entities): array
{
    return []; // TODO
}
