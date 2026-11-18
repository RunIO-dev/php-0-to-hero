<?php

declare(strict_types=1);

// What a list's address asks for (chapter 81), read from the query string, as $_GET holds it:
//   ?q=butler&available=1&sort=newest&page=2
// - q: the search, trimmed, and cut after 100 characters (characters, not bytes: "é" is one);
// - available: true only for "1"; missing or empty is false;
// - sort: one of the cases of Sort; missing or empty is Sort::Title;
// - page: a whole number from 1, written with digits only ("2", not "+2", "2.0" or " 2"); missing is 1.
// Anything else, including a value that isn't a string (?page[]=1), is an InvalidArgumentException
// whose message names the field: "sort", "page" or "available".
// link($page) is the address of another page with the same search, without what is the default:
//   ?q=butler&sort=newest&page=3, and only ?page=3 for the plain list.

enum Sort: string
{
    case Title = 'title';
    case Author = 'author';
    case Newest = 'newest';
}

final readonly class BookSearch
{
    public const int MAX = 100;

    private function __construct(
        public string $q,
        public bool $available,
        public Sort $sort,
        public int $page,
    ) {}

    /** @param array<array-key, mixed> $query */
    public static function fromQuery(array $query): self
    {
        return new self('', false, Sort::Title, 1); // TODO
    }

    public function link(int $page): string
    {
        return '?page=' . $page; // TODO
    }
}
