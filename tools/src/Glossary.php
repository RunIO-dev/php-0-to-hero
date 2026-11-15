<?php

declare(strict_types=1);

namespace Tools;

/**
 * The Arabic edition uses the agreed terms: no chapter in book/ar/ may use a
 * form that glossary/ar.json rejects (docs/standards.md §6.7).
 */
final class Glossary
{
    /** @return list<string> */
    public static function check(string $root): array
    {
        $rejected = self::rejected($root . '/glossary/ar.json');
        $problems = [];
        foreach (glob($root . '/book/ar/*.md') ?: [] as $file) {
            $text = self::plain((string) file_get_contents($file));
            foreach (preg_split('/\R/u', $text) ?: [] as $n => $line) {
                foreach ($rejected as $wrong => $term) {
                    if (preg_match('/(?<!\p{Arabic})(?:ال|و|ب|ل|ف|وال|بال|لل)?' . preg_quote($wrong, '/') . '(?!\p{Arabic})/u', $line) === 1) {
                        $problems[] = sprintf('book/ar/%s:%d: "%s" is rejected; use %s (%s)', basename($file), $n + 1, $wrong, $term['ar'], $term['en']);
                    }
                }
            }
        }

        return $problems;
    }

    /** @return array<string, array{en: string, ar: string}> rejected form => the term to use */
    public static function rejected(string $json): array
    {
        $data = json_decode((string) file_get_contents($json), true, 512, JSON_THROW_ON_ERROR);
        $out = [];
        foreach ($data['terms'] as $term) {
            foreach ($term['rejected'] as $wrong) {
                $out[self::plain($wrong)] = ['en' => $term['en'], 'ar' => $term['ar']];
            }
        }

        return $out;
    }

    /** Arabic text without harakat and tatweel, so a match does not depend on vowel marks. */
    public static function plain(string $text): string
    {
        return (string) preg_replace('/[\x{064B}-\x{0652}\x{0670}\x{0640}]/u', '', $text);
    }
}
