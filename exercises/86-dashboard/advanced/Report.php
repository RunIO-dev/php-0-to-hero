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
        return []; // TODO
    }

    /** @return array{returned: int, late: int, percent: ?int} */
    public function onTime(string $since): array
    {
        return ['returned' => 0, 'late' => 0, 'percent' => null]; // TODO
    }

    /** @return list<string> */
    public function unborrowed(string $since): array
    {
        return []; // TODO
    }
}
