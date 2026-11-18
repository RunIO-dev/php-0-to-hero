<?php

declare(strict_types=1);

namespace Shelf;

/**
 * What a list of books is filtered and sorted by (chapter 70; chapter 81 adds text and sort):
 * - text: part of the title or the author's name, in any case, or the ISBN's digits;
 * - author: part of the author's name (the API's ?author=);
 * - availableOnly: only books with a copy on the shelf;
 * - sort: the order of the list.
 * The empty query finds every book, by title.
 */
final readonly class BookQuery
{
    public function __construct(
        public string $author = '',
        public bool $availableOnly = false,
        public string $text = '',
        public BookSort $sort = BookSort::Title,
    ) {}

    public function matches(Book $book): bool
    {
        return ($this->author === '' || self::contains($book->author, $this->author))
            && ($this->text === '' || self::contains($book->title, $this->text) || self::contains($book->author, $this->text)
                || $book->isbn !== null && self::isbnDigits($this->text) !== '' && str_contains($book->isbn->digits, self::isbnDigits($this->text)))
            && (!$this->availableOnly || $book->isAvailable());
    }

    /** The digits of a search that looks like an ISBN, "978-0-..." or "9780...": else ''. */
    public static function isbnDigits(string $text): string
    {
        $digits = str_replace(['-', ' '], '', $text);

        return preg_match('/^\d{4,13}$/D', $digits) === 1 ? $digits : '';
    }

    private static function contains(string $haystack, string $needle): bool
    {
        return str_contains(mb_strtolower($haystack), mb_strtolower($needle));
    }
}
