<?php

declare(strict_types=1);

// The check behind Shelf's TranslationsTest (chapter 87), for catalogs as arrays: the English one, whose
// keys are every text, and another language's.
// - missing(): the English texts the other catalog has no translation for, in English's order.
// - values(): the names of an ICU message's values, sorted, each once: "{title}: {count, plural, one {#
//   copy} other {# copies}}" has count and title. Careful: inside a plural or select, the cases
//   ({# copy}, {a reader}) are text, and text may hold values again: "{n, plural, other {# for {name}}}".
// - wrongValues(): the English texts whose translation has other values than the English, which would
//   show a "{name}" or lose one.

final class Catalogs
{
    /**
     * @param array<string, string> $english
     * @param array<string, string> $other
     * @return list<string>
     */
    public static function missing(array $english, array $other): array
    {
        return []; // TODO
    }

    /** @return list<string> */
    public static function values(string $message): array
    {
        return []; // TODO
    }

    /**
     * @param array<string, string> $english
     * @param array<string, string> $other
     * @return list<string>
     */
    public static function wrongValues(array $english, array $other): array
    {
        return []; // TODO
    }
}
