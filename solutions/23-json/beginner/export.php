<?php

declare(strict_types=1);

/** @param list<array<string, string|int>> $books */
function booksToJson(array $books): string
{
    return json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}
