<?php

declare(strict_types=1);

// Shelf's functions. PSR-4 autoloads only classes, so composer.json lists this file under "files".

namespace Shelf;

use PDO;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

/** Two titles are the same in any case, and with spaces around them. */
function sameTitle(string $a, string $b): bool
{
    return mb_strtolower(trim($a)) === mb_strtolower(trim($b));
}

/** Text made safe to put into HTML: between tags, or in an attribute in double or single quotes. */
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Text as typed, trimmed, and with every run of spaces made one space. */
function tidy(string $text): string
{
    return (string) preg_replace('/\s+/u', ' ', trim($text));
}

/**
 * The items of a JSON file that holds a list, not yet checked.
 *
 * @return list<mixed>
 */
function readList(string $path): array
{
    $json = is_readable($path) ? file_get_contents($path) : false;
    if ($json === false) {
        throw new RuntimeException("Cannot read $path");
    }
    $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($data) || !array_is_list($data)) {
        throw new UnexpectedValueException("$path must hold a list");
    }

    return $data;
}

/**
 * The members in a JSON file: a list of objects with a name and a role.
 *
 * @return list<Member>
 */
function loadMembers(string $path): array
{
    return array_map(fn(mixed $item): Member => Member::fromArray($item), readList($path));
}

/**
 * Shelf's SQLite database in the file at $path, with its tables (chapter 54). The folder and the
 * file are made the first time; schema.sql runs every time, and its IF NOT EXISTS keeps the data.
 * Chapter 56: foreign keys are checked.
 */
function openDatabase(string $path): PDO
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0o700, true)) {
        throw new RuntimeException("Cannot make the folder $dir");
    }
    $db = new PDO("sqlite:$path", options: [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $db->exec('PRAGMA foreign_keys = ON'); // SQLite checks REFERENCES only when asked, on each connection
    $db->exec((string) file_get_contents(__DIR__ . '/../schema.sql'));

    return $db;
}

/**
 * Runs $work in a transaction (chapter 57): everything it changes is saved together at the end,
 * or, if it throws, nothing is, and the exception goes on.
 *
 * @template T
 *
 * @param callable(): T $work
 *
 * @return T
 */
function inTransaction(PDO $db, callable $work): mixed
{
    $db->beginTransaction();
    try {
        $result = $work();
        $db->commit();

        return $result;
    } catch (Throwable $e) {
        $db->rollBack();

        throw $e;
    }
}
