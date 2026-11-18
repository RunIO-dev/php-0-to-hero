<?php

declare(strict_types=1);

namespace Shelf\Web;

use Locale;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Translation\Formatter\IntlFormatterInterface;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * Arabic numbers in Western digits, 1 2 3, on every server (chapter 87). Which digits "ar" gets
 * depends on the ICU version: older ones write ١٢٣, newer ones 123, and a page could mix both. The
 * locale "ar-u-nu-latn" says it: Arabic, with Latin numbers. ICU formats in two places:
 * - the translator's messages ({count, plural, ...}, {day, date, full}): this class wraps its formatter;
 * - Twig's format_date, which takes PHP's default locale: this class sets it, after Symfony did.
 * The catalogs, the routes and the page's lang stay "ar".
 */
#[AsDecorator('translator.formatter')]
#[AutoconfigureTag('kernel.locale_aware', ['priority' => -100])]
final class WesternDigits implements MessageFormatterInterface, IntlFormatterInterface, LocaleAwareInterface
{
    private string $locale = 'en';

    public function __construct(
        #[AutowireDecorated]
        private readonly MessageFormatterInterface&IntlFormatterInterface $formatter,
    ) {}

    /** "ar" and its regions ("ar-EG") with Latin digits; every other language as it is */
    public static function locale(string $locale): string
    {
        return preg_match('/^ar([-_][A-Za-z]{2})?$/', $locale) === 1 ? "$locale-u-nu-latn" : $locale;
    }

    /** @param array<string, mixed> $parameters */
    public function format(string $message, string $locale, array $parameters = []): string
    {
        return $this->formatter->format($message, $locale, $parameters); // Symfony's own format: no numbers
    }

    /** @param array<string, mixed> $parameters */
    public function formatIntl(string $message, string $locale, array $parameters = []): string
    {
        return $this->formatter->formatIntl($message, self::locale($locale), $parameters);
    }

    /** Called for each request and email, after Symfony set PHP's default locale (priority -100). */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
        Locale::setDefault(self::locale($locale));
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
