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
        return array_map(strval(...), array_keys(array_diff_key($english, $other)));
    }

    /** @return list<string> */
    public static function values(string $message): array
    {
        $names = [];
        $isText = [true]; // for each depth of braces: text, or a value's {name, type, cases}
        $depth = 0;
        foreach (str_split($message) as $i => $char) {
            if ($char === '{') {
                $depth++;
                $isText[$depth] = !$isText[$depth - 1]; // a brace in text opens a value; in a value, a case
                if ($isText[$depth - 1] && preg_match('/\G\s*(\w+)/', $message, $name, 0, $i + 1) === 1) {
                    $names[] = $name[1];
                }
            } elseif ($char === '}') {
                $depth--;
            }
        }
        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }

    /**
     * @param array<string, string> $english
     * @param array<string, string> $other
     * @return list<string>
     */
    public static function wrongValues(array $english, array $other): array
    {
        $wrong = [];
        foreach ($english as $text => $message) {
            if (isset($other[$text]) && self::values($message) !== self::values($other[$text])) {
                $wrong[] = (string) $text;
            }
        }

        return $wrong;
    }
}
