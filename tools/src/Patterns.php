<?php

declare(strict_types=1);

namespace Tools;

/**
 * Forbidden patterns (docs/standards.md §7.5). Works on PHP tokens, so a
 * word inside a comment or a string never counts as code.
 * A "don't do this" example in code/ is named *.bad.php and is skipped.
 *
 * It is a lint, not a security review: it sees `echo $_GET[...]`, not request
 * data that was copied into a variable first, and not SQL built in pieces.
 */
final class Patterns
{
    private const REQUEST = ['$_GET', '$_POST', '$_REQUEST', '$_COOKIE', '$_SERVER', '$_FILES'];

    /** Calls and casts that make a value safe to print. */
    private const SAFE = ['htmlspecialchars', 'htmlentities', 'intval', 'json_encode', '(int)', '(float)', '(bool)'];

    /** @return list<string> */
    public static function check(string $file, string $code): array
    {
        if (str_starts_with($file, 'code/') && str_ends_with($file, '.bad.php')) {
            return [];
        }
        $problems = [];
        $add = static function (int $line, string $why) use ($file, &$problems): void {
            $problems[] = "$file:$line: $why";
        };
        $tokens = array_values(array_filter(
            \PhpToken::tokenize($code),
            static fn(\PhpToken $t): bool => !$t->is([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT]),
        ));

        foreach ($tokens as $i => $t) {
            $name = self::calledFunction($tokens, $i);

            if ($t->text === '@') {
                $add($t->line, '@ hides errors; handle the error instead');
            } elseif ($t->is(T_EVAL)) {
                $add($t->line, 'eval() runs text as code; never needed in this course');
            } elseif ($t->is(T_GLOBAL)) {
                $add($t->line, 'global variables; pass the value as a parameter');
            } elseif ($name !== null && str_starts_with($name, 'mysql_')) {
                $add($t->line, 'mysql_* was removed in PHP 7; use PDO');
            } elseif ($name === 'extract') {
                $add($t->line, 'extract() creates variables from data you do not control; read the keys you need');
            } elseif (in_array($name, ['md5', 'sha1', 'hash', 'crypt'], true) && self::nearPassword($tokens, $i)) {
                $add($t->line, "$name() is not for passwords; use password_hash()");
            } elseif (($t->is([T_ECHO, T_PRINT, T_OPEN_TAG_WITH_ECHO]) || in_array($name, ['printf', 'print_r'], true))
                && self::printsRequest($tokens, $i)) {
                $add($t->line, 'request data printed without escaping; use htmlspecialchars()');
            } elseif (self::isSql($t) && self::joinedWithVariable($tokens, $i)) {
                $add($t->line, 'SQL built from variables; use a prepared statement');
            }
        }

        return $problems;
    }

    /**
     * The lower-case name of the global function that token $i calls (also written
     * as \name), or null: a method call ($zip->extract()) or a declaration is not one.
     *
     * @param list<\PhpToken> $tokens
     */
    private static function calledFunction(array $tokens, int $i): ?string
    {
        $t = $tokens[$i];
        $prev = $tokens[$i - 1] ?? null;
        if (!$t->is([T_STRING, T_NAME_FULLY_QUALIFIED]) || ($tokens[$i + 1] ?? null)?->text !== '('
            || in_array($prev?->text, ['->', '?->', '::', 'function', 'new'], true)) {
            return null;
        }

        return strtolower(ltrim($t->text, '\\'));
    }

    /**
     * A name about passwords in the same statement: $password, $userPassword,
     * $_POST['pass'], $pwd, $secret (but not $compass).
     *
     * @param list<\PhpToken> $tokens
     */
    private static function nearPassword(array $tokens, int $i): bool
    {
        foreach (self::statement($tokens, $i) as $t) {
            if (preg_match('/(?<![a-z])pass|Pass|PASS|pwd|Pwd|PWD|secret|Secret/', $t->text) === 1) {
                return true;
            }
        }

        return false;
    }

    /** @param list<\PhpToken> $tokens */
    private static function printsRequest(array $tokens, int $i): bool
    {
        $request = false;
        foreach (self::statement($tokens, $i) as $t) {
            if (in_array($t->text, self::REQUEST, true)) {
                $request = true;
            }
            if (in_array(strtolower(ltrim($t->text, '\\')), self::SAFE, true) || $t->is([T_INT_CAST, T_DOUBLE_CAST, T_BOOL_CAST])) {
                return false;
            }
        }

        return $request;
    }

    /** A string that holds SQL, written the usual way, with keywords in capitals. */
    private static function isSql(\PhpToken $t): bool
    {
        return $t->is([T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, T_START_HEREDOC])
            && preg_match('/\b(SELECT\s.+\sFROM|INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/s', $t->text) === 1;
    }

    /**
     * A SQL string with a variable in it ("... $id", "... {$id}"), joined to a
     * variable with ".", or passed to sprintf().
     *
     * @param list<\PhpToken> $tokens
     */
    private static function joinedWithVariable(array $tokens, int $i): bool
    {
        $at = static fn(int $k): ?\PhpToken => $tokens[$k] ?? null;
        $isVariable = static fn(?\PhpToken $t): bool => $t?->is(T_VARIABLE) === true;

        return $at($i + 1)?->is([T_VARIABLE, T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES]) === true
            || $isVariable($at($i - 1))
            || ($at($i + 1)?->text === '.' && $isVariable($at($i + 2)))
            || ($at($i - 1)?->text === '.' && $isVariable($at($i - 2)))
            || ($at($i - 1)?->text === '(' && strtolower(ltrim((string) $at($i - 2)?->text, '\\')) === 'sprintf');
    }

    /**
     * The tokens of the statement that holds token $i. A statement ends at ";"
     * or at "?>", and starts after ";", "{", "}" or the opening tag.
     *
     * @param list<\PhpToken> $tokens
     * @return list<\PhpToken>
     */
    private static function statement(array $tokens, int $i): array
    {
        $start = $i;
        while ($start > 0 && !in_array($tokens[$start - 1]->text, [';', '{', '}'], true)
            && !$tokens[$start - 1]->is([T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG, T_INLINE_HTML])) {
            $start--;
        }
        $out = [];
        for ($j = $start; isset($tokens[$j]) && $tokens[$j]->text !== ';' && !$tokens[$j]->is(T_CLOSE_TAG); $j++) {
            $out[] = $tokens[$j];
        }

        return $out;
    }
}
