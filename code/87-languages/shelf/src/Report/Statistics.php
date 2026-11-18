<?php

declare(strict_types=1);

namespace Shelf\Report;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * The dashboard's numbers (chapter 86). A report reads many rows and changes none, so it asks the
 * database with SQL through Doctrine's connection, not through entities: GROUP BY counts in the
 * database, and only the answers come back, as small read-only objects. Every value is a parameter.
 * The SQL is plain enough for SQLite and MariaDB alike.
 */
final readonly class Statistics
{
    public function __construct(private Connection $db) {}

    public function overview(DateTimeImmutable $today): Overview
    {
        // One row from seven counts: subqueries, one trip to the database.
        /** @var array<string, int|string> $row SQLite gives whole numbers as int, MariaDB as string */
        $row = $this->db->fetchAssociative(
            "SELECT
                (SELECT COUNT(*) FROM books) AS titles,
                (SELECT COALESCE(SUM(copies), 0) FROM books) AS on_shelf,
                (SELECT COUNT(*) FROM loans WHERE returned_on IS NULL) AS on_loan,
                (SELECT COUNT(*) FROM loans WHERE returned_on IS NULL AND due < :today) AS overdue,
                (SELECT COUNT(*) FROM holds WHERE status = 'waiting') AS waiting,
                (SELECT COUNT(*) FROM holds WHERE status = 'ready') AS set_aside,
                (SELECT COUNT(*) FROM members) AS members",
            ['today' => $today],
            ['today' => Types::DATE_IMMUTABLE],
        ) ?: [];
        $count = fn(string $key): int => (int) ($row[$key] ?? 0);

        return new Overview($count('titles'), $count('on_shelf'), $count('on_loan'), $count('overdue'), $count('waiting'), $count('set_aside'), $count('members'));
    }

    /** @return list<Tally> the books lent most often from $from on, the most first */
    public function mostBorrowed(DateTimeImmutable $from, int $limit = 5): array
    {
        /** @var list<array{title: string, loans: int|string}> $rows */
        $rows = $this->db->fetchAllAssociative(
            'SELECT b.title, COUNT(*) AS loans
             FROM loans l JOIN books b ON b.id = l.book_id
             WHERE l.lent_on >= :from
             GROUP BY b.id, b.title
             ORDER BY loans DESC, b.title
             LIMIT :limit',
            ['from' => $from, 'limit' => $limit],
            ['from' => Types::DATE_IMMUTABLE, 'limit' => ParameterType::INTEGER],
        );

        return array_map(fn(array $row): Tally => new Tally($row['title'], (int) $row['loans']), $rows);
    }

    /**
     * @return list<Tally> the loans of each of the last $months months, the oldest first, "2026-11":
     *   chapter 87, the page writes the month's name in its own language.
     *   A month without loans has no row to GROUP BY: it is filled in here, with 0.
     */
    public function perMonth(DateTimeImmutable $today, int $months = 12): array
    {
        $first = $today->modify('first day of this month')->modify('-' . ($months - 1) . ' months');
        /** @var array<string, int|string> $counts */
        $counts = $this->db->fetchAllKeyValue(
            'SELECT SUBSTR(lent_on, 1, 7) AS month, COUNT(*) FROM loans WHERE lent_on >= :from GROUP BY month',
            ['from' => $first],
            ['from' => Types::DATE_IMMUTABLE],
        );

        $tallies = [];
        for ($month = $first, $i = 0; $i < $months; $month = $month->modify('+1 month'), $i++) {
            $tallies[] = new Tally($month->format('Y-m'), (int) ($counts[$month->format('Y-m')] ?? 0));
        }

        return $tallies;
    }

    /** The loans that came back from $from on, and how many of them after their due date. */
    public function returns(DateTimeImmutable $from): Returns
    {
        /** @var array{returned?: int|string, late?: int|string} $row */
        $row = $this->db->fetchAssociative(
            'SELECT COUNT(*) AS returned, COALESCE(SUM(CASE WHEN returned_on > due THEN 1 ELSE 0 END), 0) AS late
             FROM loans WHERE returned_on >= :from',
            ['from' => $from],
            ['from' => Types::DATE_IMMUTABLE],
        ) ?: [];

        return new Returns((int) ($row['returned'] ?? 0), (int) ($row['late'] ?? 0));
    }

    /**
     * @return list<string> the titles nobody borrowed from $since on, A to Z. LEFT JOIN keeps a book
     *   without loans, with NULLs for the loan; COUNT(l.id) counts only real loans, so it is 0 there.
     */
    public function unborrowed(DateTimeImmutable $since, int $limit = 10): array
    {
        /** @var list<string> */
        return $this->db->fetchFirstColumn(
            'SELECT b.title
             FROM books b LEFT JOIN loans l ON l.book_id = b.id AND l.lent_on >= :since
             GROUP BY b.id, b.title
             HAVING COUNT(l.id) = 0
             ORDER BY b.title
             LIMIT :limit',
            ['since' => $since, 'limit' => $limit],
            ['since' => Types::DATE_IMMUTABLE, 'limit' => ParameterType::INTEGER],
        );
    }

    /**
     * @return list<PastLoan> every loan, back or not, the newest first; only those of the book or the
     *   member, when one is given. $offset of them are skipped, for the pages.
     */
    public function history(?string $title, ?string $member, int $offset, int $limit): array
    {
        /** @var list<array{title: string, name: string, lent_on: string, due: string, returned_on: ?string}> $rows */
        $rows = $this->historyOf($title, $member)
            ->select('b.title', 'm.name', 'l.lent_on', 'l.due', 'l.returned_on')
            ->orderBy('l.lent_on', 'DESC')
            ->addOrderBy('l.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->fetchAllAssociative();

        return array_map(fn(array $row): PastLoan => new PastLoan(
            $row['title'],
            $row['name'],
            new DateTimeImmutable($row['lent_on']),
            new DateTimeImmutable($row['due']),
            $row['returned_on'] === null ? null : new DateTimeImmutable($row['returned_on']),
        ), $rows);
    }

    /** How many loans history() has to show, for the number of pages. */
    public function historyCount(?string $title, ?string $member): int
    {
        /** @var int|string $count */
        $count = $this->historyOf($title, $member)->select('COUNT(*)')->fetchOne();

        return (int) $count;
    }

    /**
     * The loans, with the filters that are given. A query with optional parts is put together by
     * Doctrine's QueryBuilder, not by joining strings: each condition is fixed text, and what was
     * typed goes in as a parameter, never into the SQL.
     */
    private function historyOf(?string $title, ?string $member): QueryBuilder
    {
        $query = $this->db->createQueryBuilder()
            ->from('loans', 'l')
            ->join('l', 'books', 'b', 'b.id = l.book_id')
            ->join('l', 'members', 'm', 'm.id = l.member_id');
        if ($title !== null && trim($title) !== '') {
            $query->andWhere('b.title = :title')->setParameter('title', trim($title)); // the column is NOCASE: any case matches
        }
        if ($member !== null && trim($member) !== '') {
            $query->andWhere('m.name = :member')->setParameter('member', trim($member));
        }

        return $query;
    }
}
