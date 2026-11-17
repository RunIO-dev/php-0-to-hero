<?php

declare(strict_types=1);

// Helpers for this chapter's examples, as in chapter 53: they run SQL and print the answer.

/** A new database in memory, with the tables and rows of an .sql file. It is gone when the script ends. */
function database(string $sqlFile): PDO
{
    $db = new PDO('sqlite::memory:');
    $db->exec((string) file_get_contents($sqlFile));

    return $db;
}

/** Runs one SQL statement and prints it with the answer: rows as a table, or how many rows changed. */
function show(PDO $db, string $sql): void
{
    echo "> $sql\n";
    try {
        $result = $db->query($sql);
    } catch (PDOException $e) {
        echo 'Error: ' . ($e->errorInfo[2] ?? $e->getMessage()) . "\n\n";

        return;
    }
    if ($result === false) {
        return;
    }
    if ($result->columnCount() === 0) {
        echo $result->rowCount() . " row(s) changed\n\n";

        return;
    }
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    echo $rows === [] ? "(no rows)\n\n" : table($rows) . "\n";
}

/** @param non-empty-list<array<string, mixed>> $rows */
function table(array $rows): string
{
    $text = static fn(mixed $value): string => $value === null ? 'NULL' : (is_scalar($value) ? (string) $value : '?');
    $widths = [];
    foreach (array_keys($rows[0]) as $column) {
        $widths[$column] = max(mb_strlen($column), ...array_map(static fn(array $row): int => mb_strlen($text($row[$column])), $rows));
    }
    $line = static fn(array $cells): string => rtrim(implode(' | ', array_map(
        static fn(string $column): string => mb_str_pad($cells[$column], $widths[$column]),
        array_keys($widths),
    ))) . "\n";
    $out = $line(array_combine(array_keys($widths), array_keys($widths)));
    $out .= implode('-+-', array_map(static fn(int $width): string => str_repeat('-', $width), $widths)) . "\n";
    foreach ($rows as $row) {
        $out .= $line(array_map($text, $row));
    }

    return $out;
}
