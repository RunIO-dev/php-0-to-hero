<?php

declare(strict_types=1);

// Shelf's functions. PSR-4 autoloads only classes, so composer.json lists this file under "files".

namespace Shelf;

use RuntimeException;
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
