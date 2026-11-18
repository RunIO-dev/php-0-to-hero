<?php

declare(strict_types=1);

// The pattern for a search with LIKE (chapter 81): "%part%", in lower case, so
// WHERE LOWER(title) LIKE :pattern ESCAPE '\' finds the text anywhere, in any case.
// LIKE has two wildcards of its own, % (any letters) and _ (one letter), and \ is the escape letter
// here: each of the three that the visitor types gets a \ before it, so it is only a letter.
//   likePattern('Butler') === '%butler%'
//   likePattern('50%')    === '%50\%%'

function likePattern(string $text): string
{
    return '%' . addcslashes(mb_strtolower($text), '%_\\') . '%';
}
