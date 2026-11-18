<?php

declare(strict_types=1);

namespace Shelf\Tests;

use MessageFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Every text Shelf shows has its German and Arabic (chapter 87). The test finds the texts where the code
 * has them: `'…'|trans` and `text: '…'` in the templates, t() and TranslatableMessage in PHP, and the
 * refusals Shelf throws. A new text without a translation fails here, not on a German page in English.
 */
final class TranslationsTest extends TestCase
{
    private const string ROOT = __DIR__ . '/..';

    /** @return list<string> the texts, in English, which are the catalogs' keys */
    private static function texts(): array
    {
        $texts = [];
        foreach (new Finder()->files()->in(self::ROOT . '/templates')->name('*.twig') as $file) {
            preg_match_all("{'((?:[^'\\\\]|\\\\.)*)'\\s*\\|\\s*trans\\b|\"((?:[^\"\\\\]|\\\\.)*)\"\\s*\\|\\s*trans\\b|\\btext:\\s*'((?:[^'\\\\]|\\\\.)*)'}", $file->getContents(), $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);
            foreach ($matches as $match) {
                $texts[] = stripslashes($match[1] ?? $match[2] ?? $match[3] ?? '');
            }
        }
        foreach (new Finder()->files()->in(self::ROOT . '/src')->name('*.php') as $file) {
            preg_match_all("{\\b(?:new\\s+)?([A-Z]\\w*|t)\\(\\s*'((?:[^'\\\\]|\\\\.)*)'}", $file->getContents(), $matches, PREG_SET_ORDER);
            foreach ($matches as [, $class, $text]) {
                if (self::isTranslatable($class)) {
                    $texts[] = stripslashes($text);
                }
            }
        }

        return array_values(array_unique($texts));
    }

    /** t(), a TranslatableMessage, or one of Shelf's refusals, which are TranslatableInterface too */
    private static function isTranslatable(string $class): bool
    {
        return in_array($class, ['t', 'TranslatableMessage', 'CustomUserMessageAccountStatusException'], true)
            || class_exists("Shelf\\$class") && is_subclass_of("Shelf\\$class", TranslatableInterface::class);
    }

    /** @return array<string, string> */
    private static function catalog(string $name): array
    {
        $catalog = Yaml::parseFile(self::ROOT . "/translations/$name.yaml");
        self::assertIsArray($catalog);
        $texts = [];
        foreach ($catalog as $english => $translated) {
            self::assertIsString($translated);
            $texts[(string) $english] = $translated;
        }

        return $texts;
    }

    /** @return iterable<string, array{string}> */
    public static function languages(): iterable
    {
        foreach (['en', 'de', 'ar'] as $language) {
            yield $language => [$language];
        }
    }

    #[DataProvider('languages')]
    public function testEveryTextIsTranslated(string $language): void
    {
        $catalog = self::catalog("messages+intl-icu.$language");

        self::assertGreaterThan(200, count(self::texts()), 'The scan finds the texts');
        self::assertSame([], array_values(array_diff(self::texts(), array_keys($catalog))), "Texts without a translation into $language");
        self::assertSame([], array_values(array_diff(array_keys($catalog), self::texts())), 'Translations of texts that are gone');
    }

    #[DataProvider('languages')]
    public function testATranslationHasTheSameValuesAndIsValidIcu(string $language): void
    {
        foreach (self::catalog("messages+intl-icu.$language") as $english => $translated) {
            self::assertInstanceOf(MessageFormatter::class, MessageFormatter::create($language, $translated), "Not ICU: $translated");
            self::assertSame(self::arguments($english), self::arguments($translated), "The values of: $english");
        }
    }

    public function testEnglishIsTheKeyItself(): void
    {
        foreach (self::catalog('messages+intl-icu.en') as $english => $translated) {
            self::assertSame($english, $translated);
        }
    }

    public function testTheValidationMessagesAreTranslated(): void
    {
        $messages = [];
        foreach (new Finder()->files()->in(self::ROOT . '/src')->name('*.php') as $file) {
            preg_match_all("{(?:\\w*[mM]essage:\\s*|'invalid_message'\\s*=>\\s*|public string \\\$message = )'((?:[^'\\\\]|\\\\.)*)'}", $file->getContents(), $matches);
            array_push($messages, ...array_map(stripslashes(...), $matches[1]));
        }
        self::assertCount(10, array_unique($messages));
        foreach (['de', 'ar'] as $language) {
            $catalog = self::catalog("validators.$language");
            self::assertEqualsCanonicalizing(array_unique($messages), array_keys($catalog), "validators.$language");
            foreach ($catalog as $english => $translated) {
                preg_match_all('/\{\{ \w+ \}\}/', $english, $want);
                preg_match_all('/\{\{ \w+ \}\}/', $translated, $have);
                self::assertEqualsCanonicalizing($want[0], $have[0], "The values of: $english");
            }
        }
    }

    /**
     * The names of an ICU message's values, such as title and count in "{title}: {count, plural, …}". Inside
     * plural and select, the cases ({# copy}, {a reader}) are text again, which may hold more values.
     *
     * @return list<string>
     */
    private static function arguments(string $message): array
    {
        $names = [];
        $depth = 0;
        $text = [true]; // per depth: is it text (true) or an argument (false)?
        $length = strlen($message);
        for ($i = 0; $i < $length; $i++) {
            if ($message[$i] === '{') {
                $depth++;
                if ($text[$depth - 1]) {
                    preg_match('/\G\s*(\w+)/', $message, $name, 0, $i + 1);
                    $names[] = $name[1] ?? '';
                    $text[$depth] = false;
                } else {
                    $text[$depth] = true; // a case of a plural or select
                }
            } elseif ($message[$i] === '}') {
                $depth--;
            }
        }
        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }
}
