<?php

declare(strict_types=1);

namespace Shelf\Twig;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Twig\Attribute\AsTwigFilter;

/**
 * Values that pages show, worked out in PHP instead of in templates (chapter 80). Each method with
 * #[AsTwigFilter] is a Twig filter: {{ book.title|tone }}. Symfony finds the class on its own.
 * The clock is Symfony's: the real time in the app, a fixed one in tests.
 */
final readonly class ShelfExtension
{
    /** The colours of the placeholder covers, in the stylesheet: tone-0 to tone-5. */
    private const int TONES = 6;

    public function __construct(private ClockInterface $clock) {}

    /** The same title always gets the same colour, so a book is easy to find again. */
    #[AsTwigFilter('tone')]
    public function tone(string $title): int
    {
        return crc32($title) % self::TONES;
    }

    /**
     * "due today", "due in 3 days", "2 days overdue": how a due date reads today. Chapter 87: a message
     * to translate, not a text; |trans in the page says it in the page's language, with the plural
     * forms of that language (Arabic has six).
     */
    #[AsTwigFilter('due')]
    public function due(DateTimeImmutable $due): TranslatableMessage
    {
        $days = $this->daysLeft($due);

        return $days >= 0
            ? new TranslatableMessage('{days, plural, =0 {due today} =1 {due tomorrow} other {due in # days}}', ['days' => $days])
            : new TranslatableMessage('{days, plural, one {# day overdue} other {# days overdue}}', ['days' => -$days]);
    }

    /** The badge's colour for a due date: overdue is danger, the next two days a warning. */
    #[AsTwigFilter('due_level')]
    public function dueLevel(DateTimeImmutable $due): string
    {
        $days = $this->daysLeft($due);

        return $days < 0 ? 'danger' : ($days <= 2 ? 'warn' : 'ok');
    }

    /** Whole days from today to the due date: negative once it has passed. */
    private function daysLeft(DateTimeImmutable $due): int
    {
        $today = $this->clock->now()->setTime(0, 0);

        return (int) $today->diff($due->setTime(0, 0))->format('%r%a');
    }
}
