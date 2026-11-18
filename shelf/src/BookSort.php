<?php

declare(strict_types=1);

namespace Shelf;

use Symfony\Component\Translation\TranslatableMessage;

/**
 * The orders a list of books can have (chapter 81). The value is what the address says, ?sort=newest;
 * the label is what the form shows. One enum for both the database and the in-memory list, so the
 * two can't sort differently.
 */
enum BookSort: string
{
    case Title = 'title';
    case Author = 'author';
    case Newest = 'newest';
    case Oldest = 'oldest';

    /** Chapter 87: a message, which the page translates. */
    public function label(): TranslatableMessage
    {
        return match ($this) {
            self::Title => new TranslatableMessage('Title, A to Z'),
            self::Author => new TranslatableMessage('Author, A to Z'),
            self::Newest => new TranslatableMessage('Newest first'),
            self::Oldest => new TranslatableMessage('Oldest first'),
        };
    }

    /**
     * The fields to sort by, in order: the title always comes last, so books of the same author or year
     * keep one order, and a page never shows a book twice or skips one.
     *
     * @return non-empty-array<'title'|'author'|'year', 'ASC'|'DESC'>
     */
    public function fields(): array
    {
        return match ($this) {
            self::Title => ['title' => 'ASC'],
            self::Author => ['author' => 'ASC', 'title' => 'ASC'],
            self::Newest => ['year' => 'DESC', 'title' => 'ASC'],
            self::Oldest => ['year' => 'ASC', 'title' => 'ASC'],
        };
    }

    /** The same order for books in memory: negative when $a comes first. */
    public function compare(Book $a, Book $b): int
    {
        foreach ($this->fields() as $field => $direction) {
            $order = match ($field) {
                'year' => $a->year <=> $b->year,
                'author' => strcasecmp($a->author, $b->author),
                'title' => strcasecmp($a->title, $b->title),
            };
            if ($order !== 0) {
                return $direction === 'ASC' ? $order : -$order;
            }
        }

        return 0;
    }
}
