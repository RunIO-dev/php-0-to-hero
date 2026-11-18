<?php

declare(strict_types=1);

// Before CI deploys on its own (chapter 79), it reads the new migrations: a statement that removes or
// renames data needs a person, a backup and a plan. needsCare($sql) takes a migration's SQL and returns
// those statements, in their order, each on one line: trimmed, every run of white space as one space,
// without its ";". Words in upper or lower case alike. A statement needs care when it:
// - drops a table, an index or a column:      DROP TABLE ..., DROP INDEX ..., ALTER TABLE ... DROP ...
// - renames:                                  ALTER TABLE ... RENAME ..., RENAME TABLE ...
// - empties a table:                          TRUNCATE ...
// - deletes or updates every row:             DELETE FROM ... or UPDATE ... without WHERE
// Statements end at ";". A ";" or a word inside a string ('...', with '' for a quote) or a comment
// (-- to the end of the line, or /* ... */) is not SQL: it ends nothing, and needs no care. Comments are
// left out of the statements returned; strings stay as they were written.

/** @return list<string> */
function needsCare(string $sql): array
{
    return array_values(array_filter(statements($sql), isRisky(...)));
}

/**
 * The statements, one line each, without comments; ";" in strings and comments ends nothing.
 *
 * @return list<string>
 */
function statements(string $sql): array
{
    $statements = [];
    $current = '';
    $length = strlen($sql);
    for ($i = 0; $i < $length; $i++) {
        $rest = substr($sql, $i, 2);
        if ($sql[$i] === "'") {                       // a string, with '' for a quote inside it
            $end = $i + 1;
            while ($end < $length && ($sql[$end] !== "'" || ($sql[$end + 1] ?? '') === "'")) {
                $end += $sql[$end] === "'" ? 2 : 1;
            }
            $current .= substr($sql, $i, $end - $i + 1);
            $i = $end;
        } elseif ($rest === '--' || $rest === '/*') {    // a comment: a space in its place
            $end = $rest === '--' ? strpos($sql, "\n", $i) : strpos($sql, '*/', $i + 2);
            $i = $end === false ? $length : ($rest === '--' ? $end : $end + 1);
            $current .= ' ';
        } elseif ($sql[$i] === ';') {
            $statements[] = $current;
            $current = '';
        } else {
            $current .= $sql[$i];
        }
    }
    $statements[] = $current;
    $lines = array_map(static fn(string $s): string => trim((string) preg_replace('/\s+/', ' ', $s)), $statements);

    return array_values(array_filter($lines, static fn(string $s): bool => $s !== ''));
}

/** Whether a statement removes or renames data, judged on its SQL alone, with every string emptied. */
function isRisky(string $statement): bool
{
    $sql = ' ' . strtoupper((string) preg_replace("/'(?:[^']|'')*'/", "''", $statement)) . ' ';

    return preg_match('/^ (DROP (TABLE|INDEX)|TRUNCATE|RENAME TABLE) /', $sql) === 1
        || preg_match('/^ ALTER TABLE .* (DROP|RENAME) /', $sql) === 1
        || (preg_match('/^ (DELETE FROM|UPDATE) /', $sql) === 1 && !str_contains($sql, ' WHERE '));
}
