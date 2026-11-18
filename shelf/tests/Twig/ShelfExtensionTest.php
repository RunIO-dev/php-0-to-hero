<?php

declare(strict_types=1);

namespace Shelf\Tests\Twig;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Twig\ShelfExtension;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

/** Shelf's Twig filters (chapter 80), with a clock that stands still. Chapter 87: in three languages. */
final class ShelfExtensionTest extends TestCase
{
    /** @return iterable<string, array{string, string, string}> today, then the label and level of a loan due on 1 December */
    public static function days(): iterable
    {
        yield 'a week before' => ['2026-11-24 09:30', 'due in 7 days', 'ok'];
        yield 'three days before' => ['2026-11-28 23:59', 'due in 3 days', 'ok'];
        yield 'two days before' => ['2026-11-29 00:00', 'due in 2 days', 'warn'];
        yield 'the day before' => ['2026-11-30 18:00', 'due tomorrow', 'warn'];
        yield 'the day' => ['2026-12-01 23:00', 'due today', 'warn'];
        yield 'one day late' => ['2026-12-02 08:00', '1 day overdue', 'danger'];
        yield 'two weeks late' => ['2026-12-15 08:00', '14 days overdue', 'danger'];
    }

    #[DataProvider('days')]
    public function testADueDateReadsAsOfToday(string $today, string $label, string $level): void
    {
        $extension = new ShelfExtension(new MockClock($today));
        $due = new DateTimeImmutable('2026-12-01');

        self::assertSame($label, $extension->due($due)->trans(self::translator()));
        self::assertSame($level, $extension->dueLevel($due));
    }

    /** @return iterable<string, array{string, string, string}> the language, today, and the label then */
    public static function languages(): iterable
    {
        yield 'German, tomorrow' => ['de', '2026-11-30 18:00', 'morgen fällig'];
        yield 'German, in a week' => ['de', '2026-11-24 09:30', 'fällig in 7 Tagen'];
        yield 'German, a day late' => ['de', '2026-12-02 08:00', '1 Tag überfällig'];
        yield 'Arabic, two days late' => ['ar', '2026-12-03 08:00', 'متأخر يومين'];
        yield 'Arabic, three days late' => ['ar', '2026-12-04 08:00', 'متأخر 3 أيام'];
        yield 'Arabic, eleven days late' => ['ar', '2026-12-12 08:00', 'متأخر 11 يومًا'];
    }

    /** Chapter 87: the same message, in each language's words and plural forms. */
    #[DataProvider('languages')]
    public function testADueDateReadsInEachLanguage(string $locale, string $today, string $label): void
    {
        $extension = new ShelfExtension(new MockClock($today));

        self::assertSame($label, $extension->due(new DateTimeImmutable('2026-12-01'))->trans(self::translator(), $locale));
    }

    /** Shelf's own translations, without the rest of Symfony. */
    private static function translator(): Translator
    {
        $translator = new Translator('en');
        $translator->addLoader('yaml', new YamlFileLoader());
        foreach (['en', 'de', 'ar'] as $locale) {
            $translator->addResource('yaml', dirname(__DIR__, 2) . "/translations/messages+intl-icu.$locale.yaml", $locale, 'messages+intl-icu');
        }

        return $translator;
    }

    public function testATitleAlwaysGetsTheSameTone(): void
    {
        $extension = new ShelfExtension(new MockClock());

        self::assertSame($extension->tone('Kindred'), $extension->tone('Kindred'));
        foreach (['Kindred', 'Sula', 'Kitchen', 'Beloved', ''] as $title) {
            self::assertContains($extension->tone($title), range(0, 5));
        }
    }
}
