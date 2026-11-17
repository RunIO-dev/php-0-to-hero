<?php

declare(strict_types=1);

namespace Shelf;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Shelf\Doctrine\IsbnType;
use UnexpectedValueException;

/**
 * One book on the shelf. Only the book changes its number of copies, so it never goes below 0.
 * Chapter 74: an entity. The attributes tell Doctrine which table and columns hold it; the class
 * itself, with its rules, stays as it was. The title is unique in any case (NOCASE, SQLite's own).
 */
#[ORM\Entity]
#[ORM\Table(name: 'books')]
final class Book
{
    public const int FIRST_YEAR = 1;
    public const int LAST_YEAR = 2100;

    /** The row's number, which the database gives a new book; null until it is stored. */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(unique: true, options: ['collation' => 'NOCASE'])]
        public readonly string $title,
        #[ORM\Column]
        public readonly string $author,
        #[ORM\Column]
        public readonly int $year,
        #[ORM\Column]
        public private(set) int $copies = 1,
        #[ORM\Column(type: IsbnType::NAME, nullable: true, unique: true)]
        public readonly ?Isbn $isbn = null,
    ) {
        if ($copies < 0) {
            throw new InvalidArgumentException("\"$title\" can't have $copies copies");
        }
        if ($year < self::FIRST_YEAR || $year > self::LAST_YEAR) {
            throw new InvalidArgumentException("\"$title\" can't be from the year $year");
        }
    }

    /** A book from the data in books.json, checked: the file is input, like anything from outside. */
    public static function fromArray(mixed $data): self
    {
        $isbn = is_array($data) ? $data['isbn'] ?? null : null;
        if (!is_array($data) || !is_string($data['title'] ?? null) || !is_string($data['author'] ?? null)
            || !is_int($data['year'] ?? null) || !is_int($data['copies'] ?? null) || !($isbn === null || is_string($isbn))) {
            throw new UnexpectedValueException('Each book needs a title, an author, a year and copies: ' . json_encode($data));
        }

        return new self($data['title'], $data['author'], $data['year'], $data['copies'], $isbn === null ? null : new Isbn($isbn));
    }

    /** @return array{title: string, author: string, year: int, copies: int, isbn: ?string} the data that fromArray reads */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'year' => $this->year,
            'copies' => $this->copies,
            'isbn' => $this->isbn?->digits,
        ];
    }

    public function isAvailable(): bool
    {
        return $this->copies > 0;
    }

    public function lend(): void
    {
        if (!$this->isAvailable()) {
            throw new NoCopyLeft("All copies of \"$this->title\" are on loan");
        }
        $this->copies--;
    }

    /** The library now has $copies copies of this book (chapter 55): more were bought, or some were lost. */
    public function changeCopies(int $copies): void
    {
        if ($copies < 0) {
            throw new InvalidArgumentException("\"$this->title\" can't have $copies copies");
        }
        $this->copies = $copies;
    }

    public function line(): string
    {
        $status = $this->isAvailable() ? 'available' : 'all copies on loan';

        return "- $this->title ($this->author, $this->year): $status";
    }
}
