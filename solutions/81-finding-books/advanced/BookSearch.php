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
        // Each value is a string, or missing (''); ?page[]=1 makes an array, which is a mistake.
        $text = static function (string $field) use ($query): string {
            $value = $query[$field] ?? '';

            return is_string($value) ? $value : throw new InvalidArgumentException("$field must be text");
        };
        $available = $text('available');
        if (!in_array($available, ['', '1'], true)) {
            throw new InvalidArgumentException('available is 1, or left out');
        }
        $sort = $text('sort') === '' ? Sort::Title : Sort::tryFrom($text('sort'))
            ?? throw new InvalidArgumentException('sort is one of: ' . implode(', ', array_column(Sort::cases(), 'value')));
        $page = array_key_exists('page', $query) ? $text('page') : '1';
        if (preg_match('/^[1-9]\d{0,8}$/D', $page) !== 1) {
            throw new InvalidArgumentException('page is a whole number from 1');
        }

        return new self(mb_substr(trim($text('q')), 0, self::MAX), $available === '1', $sort, (int) $page);
    }

    public function link(int $page): string
    {
        $keep = array_filter(['q' => $this->q, 'available' => $this->available ? '1' : '', 'sort' => $this->sort === Sort::Title ? '' : $this->sort->value]);

        return '?' . http_build_query($keep + ['page' => $page]);
    }
}
