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
    /** @var list<Loan> */
    return $entities->createQuery('SELECT l, b FROM Desk\Loan l JOIN l.book b WHERE l.due < :today ORDER BY l.due')
        ->setParameter('today', $today, 'date_immutable')
        ->getResult();
}

/**
 * The titles a member has on loan, the soonest due first.
 *
 * @return list<string>
 */
function titlesOf(EntityManagerInterface $entities, string $member): array
{
    return array_column($entities->createQuery('SELECT b.title FROM Desk\Loan l JOIN l.book b WHERE l.member = :member ORDER BY l.due')
        ->setParameter('member', $member)
        ->getArrayResult(), 'title');
}

/**
 * How many loans each book has, the most first, then by title: title => count. Books on no loan are left out.
 *
 * @return array<string, int>
 */
function busiest(EntityManagerInterface $entities): array
{
    $rows = $entities->createQuery('SELECT b.title, COUNT(l.id) AS loans FROM Desk\Loan l JOIN l.book b GROUP BY b.id, b.title ORDER BY loans DESC, b.title')
        ->getArrayResult();

    return array_map(intval(...), array_column($rows, 'loans', 'title'));
}
