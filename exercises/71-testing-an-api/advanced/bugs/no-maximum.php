<?php

declare(strict_types=1);

// Bug: per_page has no maximum: anyone can ask for everything.

// The library's list of books, a page at a time, as a function: what GET /api/books answers.

const SHELF = [
    ['title' => 'Beloved', 'author' => 'Toni Morrison', 'year' => 1987],
    ['title' => 'Dawn', 'author' => 'Octavia E. Butler', 'year' => 1987],
    ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979],
    ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988],
    ['title' => 'Parable of the Sower', 'author' => 'Octavia E. Butler', 'year' => 1993],
    ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => 1973],
    ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958],
];

const SORTS = ['title', 'year'];

/**
 * ?author= (part of the name, any case), ?sort=title (the default) or ?sort=year (then by title),
 * ?page= (from 1) and ?per_page= (1 to 5, the default 3). The answer: 200, a JSON list of books, the header
 * X-Total-Count (how many books match the author), and Link with first, prev, next and last.
 * Anything wrong: 400, problem details.
 *
 * @param array<string, string> $query
 * @return array{status: int, headers: array<string, string>, body: string}
 */
function booksAnswer(array $query): array
{
    $page = $query['page'] ?? '1';
    $perPage = $query['per_page'] ?? '3';
    $sort = $query['sort'] ?? 'title';
    if (!ctype_digit($page) || (int) $page < 1 || !ctype_digit($perPage) || (int) $perPage < 1 || !in_array($sort, SORTS, true)) {
        $problem = ['type' => 'about:blank', 'title' => 'Bad Request', 'status' => 400, 'detail' => 'page from 1, per_page from 1 to 5, sort title or year.'];

        return ['status' => 400, 'headers' => ['Content-Type' => 'application/problem+json'], 'body' => json_encode($problem, JSON_THROW_ON_ERROR)];
    }
    [$page, $perPage, $author] = [(int) $page, (int) $perPage, mb_strtolower($query['author'] ?? '')];

    $found = array_values(array_filter(SHELF, static fn(array $book): bool => str_contains(mb_strtolower($book['author']), $author)));
    usort($found, static fn(array $a, array $b): int => $sort === 'year' ? [$a['year'], $a['title']] <=> [$b['year'], $b['title']] : $a['title'] <=> $b['title']);
    $pages = max(1, (int) ceil(count($found) / $perPage));

    $link = static fn(int $to, string $rel): string => '</api/books?' . http_build_query(['page' => $to, 'per_page' => $perPage]) . ">; rel=\"$rel\"";
    $links = [$link(1, 'first')];
    if ($page > 1) {
        $links[] = $link(min($page - 1, $pages), 'prev');
    }
    if ($page < $pages) {
        $links[] = $link($page + 1, 'next');
    }
    $links[] = $link($pages, 'last');

    return [
        'status' => 200,
        'headers' => ['Content-Type' => 'application/json', 'X-Total-Count' => (string) count($found), 'Link' => implode(', ', $links)],
        'body' => json_encode(array_slice($found, ($page - 1) * $perPage, $perPage), JSON_THROW_ON_ERROR),
    ];
}
