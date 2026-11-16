<?php

declare(strict_types=1);

/** @return list<string> */
function titlesFromJson(string $json): array
{
    $books = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

    return array_map(fn(array $book): string => (string) $book['title'], $books);
}
