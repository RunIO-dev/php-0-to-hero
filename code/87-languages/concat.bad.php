<?php

declare(strict_types=1);

// A sentence made of pieces (chapter 87): fine in English, and impossible to translate. The pieces
// are translated one by one, in English order, with English's two plural forms.

function shelfLine(string $title, int $copies): string
{
    return $copies . ' ' . ($copies === 1 ? 'copy' : 'copies') . ' of "' . $title . '" on the shelf';
}

echo shelfLine('Kindred', 1), "\n";
echo shelfLine('Kindred', 3), "\n";
