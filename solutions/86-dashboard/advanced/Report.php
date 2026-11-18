<?php

declare(strict_types=1);

// A report on a library's loans (chapter 86), with the tables of the test:
//   books (id INTEGER PRIMARY KEY, title TEXT)
//   loans (id INTEGER PRIMARY KEY, book_id INTEGER, member TEXT, lent_on TEXT, due TEXT, returned_on TEXT)
// Dates are "Y-m-d" text, so they compare as dates. Every method is one query, with its values as
// parameters; PHP only shapes the answer.
// - popular($since, $limit): [title => loans] of the books lent from $since on, the most first, equal
//   counts A to Z, at most $limit.
// - onTime($since): of the loans that came back from $since on, how many, how many after their due
//   date, and the whole percent on time: [returned, late, percent]; percent is null if none came back.
// - unborrowed($since): the titles nobody borrowed from $since on, A to Z, books never lent included.

final readonly class Report
{
    public function __construct(private PDO $db) {}

    /** @return array<string, int> */
    public function popular(string $since, int $limit): array
    {
        $query = $this->db->prepare('SELECT b.title, COUNT(*) AS loans
            FROM loans l JOIN books b ON b.id = l.book_id
            WHERE l.lent_on >= :since
            GROUP BY b.id, b.title
            ORDER BY loans DESC, b.title
            LIMIT :limit');
        $query->bindValue('since', $since);
        $query->bindValue('limit', $limit, PDO::PARAM_INT);
        $query->execute();

        return array_map(intval(...), $query->fetchAll(PDO::FETCH_KEY_PAIR));
    }

    /** @return array{returned: int, late: int, percent: ?int} */
    public function onTime(string $since): array
    {
        $query = $this->db->prepare('SELECT COUNT(*) AS returned, COALESCE(SUM(CASE WHEN returned_on > due THEN 1 ELSE 0 END), 0) AS late
            FROM loans WHERE returned_on >= ?');
        $query->execute([$since]);
        /** @var array{returned: int|string, late: int|string} $row */
        $row = $query->fetch(PDO::FETCH_ASSOC);
        [$returned, $late] = [(int) $row['returned'], (int) $row['late']];

        return [
            'returned' => $returned,
            'late' => $late,
            'percent' => $returned === 0 ? null : (int) round(100 * ($returned - $late) / $returned),
        ];
    }

    /** @return list<string> */
    public function unborrowed(string $since): array
    {
        // The date goes into the JOIN, not into WHERE: a WHERE on l.lent_on drops the books without loans.
        $query = $this->db->prepare('SELECT b.title
            FROM books b LEFT JOIN loans l ON l.book_id = b.id AND l.lent_on >= ?
            GROUP BY b.id, b.title
            HAVING COUNT(l.id) = 0
            ORDER BY b.title');
        $query->execute([$since]);

        /** @var list<string> */
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }
}
