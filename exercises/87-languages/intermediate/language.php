<?php

declare(strict_types=1);

// The language of an address (chapter 87), as Shelf's routes read it: "/de/books" is German and
// "/books", "/ar" is Arabic and "/". An address without a known language at its start is English,
// and stays as it is: "/desk", "/fr/desk", "/details" (it starts with "/de", but not with "/de/").
// Return [language, the rest of the address].

/**
 * @param list<string> $languages the ones besides English, such as ['de', 'ar']
 * @return array{string, string}
 */
function language(string $path, array $languages): array
{
    return ['en', $path]; // TODO
}
