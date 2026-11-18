<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use Locale;
use Shelf\Web\WesternDigits;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Arabic pages write numbers as 1 2 3 (chapter 87), whichever ICU the server has: an older one gives
 * "ar" the digits ١٢٣, and a page mixed both. Every assertion here fails on such a server without
 * WesternDigits, and none of them can tell on a newer one: so they check the output, not ICU.
 */
final class WesternDigitsTest extends ShelfWebTestCase
{
    private const string EASTERN = '/[٠-٩]/u';

    public function testOnlyArabicGetsLatinDigits(): void
    {
        self::assertSame('ar-u-nu-latn', WesternDigits::locale('ar'));
        self::assertSame('ar-EG-u-nu-latn', WesternDigits::locale('ar-EG'));
        self::assertSame('de', WesternDigits::locale('de'));
        self::assertSame('en', WesternDigits::locale('en'));
    }

    /** Egyptian Arabic gets ١٢٣ from every ICU: the one case this test's own ICU can show. */
    public function testTheFormatterSwitchesTheDigits(): void
    {
        $plain = new MessageFormatter();
        $western = new WesternDigits($plain);
        $message = '{n, number} · {day, date, full}';
        $values = ['n' => 1234, 'day' => new DateTimeImmutable('2026-03-16')];

        self::assertMatchesRegularExpression(self::EASTERN, $plain->formatIntl($message, 'ar-EG', $values), 'Without it');
        self::assertDoesNotMatchRegularExpression(self::EASTERN, $western->formatIntl($message, 'ar-EG', $values));
        self::assertStringContainsString('1,234', $western->formatIntl($message, 'ar-EG', $values));
    }

    public function testTheTranslatorsCountsAndDates(): void
    {
        $text = self::service(TranslatorInterface::class)->trans('Lent: {title} to {member}, due back on {due, date, full}.', ['title' => 'Kindred', 'member' => 'Amira', 'due' => new DateTimeImmutable('2026-03-16')], locale: 'ar');
        $count = self::service(TranslatorInterface::class)->trans('{count, plural, one {# book} other {# books}}', ['count' => 1234], locale: 'ar');

        self::assertStringContainsString('16', $text);
        self::assertStringContainsString('1,234', $count);
    }

    public function testThePagesDates(): void
    {
        $this->client->request('GET', '/ar/');

        self::assertDoesNotMatchRegularExpression(self::EASTERN, (string) $this->client->getResponse()->getContent());
    }

    /** The same as for a page: Symfony switches the locale, then WesternDigits sets PHP's default. */
    public function testAnEmailsDates(): void
    {
        self::service(LocaleSwitcher::class)->runWithLocale('ar', function (): void {
            self::assertSame('ar-u-nu-latn', Locale::getDefault(), 'The worker renders emails in the member\'s language');
        });
    }
}
