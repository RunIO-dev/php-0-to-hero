<?php

declare(strict_types=1);

namespace Tools;

/**
 * PHPStan reads all files of one run as one program, so two examples that both
 * define lateFee() would be mixed up. This puts files into batches in which no
 * function or class name is declared twice; each batch is one PHPStan run.
 */
final class Batches
{
    /**
     * @param list<string> $files
     * @return list<list<string>>
     */
    public static function split(array $files): array
    {
        $batches = [];
        $names = [];
        foreach ($files as $file) {
            $declared = self::declared((string) file_get_contents($file));
            foreach ($batches as $i => $batch) {
                if (array_intersect($declared, $names[$i]) === []) {
                    $batches[$i][] = $file;
                    $names[$i] = [...$names[$i], ...$declared];
                    continue 2;
                }
            }
            $batches[] = [$file];
            $names[] = $declared;
        }

        return array_values($batches);
    }

    /** @return list<string> the lower-case names of the functions and classes the code declares */
    public static function declared(string $code): array
    {
        $tokens = array_values(array_filter(
            \PhpToken::tokenize($code),
            static fn(\PhpToken $t): bool => !$t->is([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT]),
        ));
        $names = [];
        foreach ($tokens as $i => $t) {
            $next = $tokens[$i + 1] ?? null;
            if ($t->is([T_FUNCTION, T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM]) && $next?->is(T_STRING) === true
                && ($tokens[$i - 1] ?? null)?->is(T_DOUBLE_COLON) !== true) {
                $names[] = strtolower($next->text);
            }
        }

        return array_values(array_unique($names));
    }
}
