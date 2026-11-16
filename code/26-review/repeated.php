<?php

declare(strict_types=1);

// Three functions, three copies of the same idea: titles are equal in any case.
function hasTitle(string $title, string $wanted): bool
{
    return mb_strtolower($title) === mb_strtolower($wanted);
}

function isDuplicate(string $title, string $existing): bool
{
    return mb_strtolower(trim($title)) === mb_strtolower($existing);
}

function isWanted(string $title, string $wanted): bool
{
    return strtolower($title) === strtolower($wanted);
}

// The copies have drifted apart: one trims, one is not multibyte-safe.
var_dump(hasTitle('Über die Liebe', 'über die liebe'));
var_dump(isDuplicate('Über die Liebe ', 'über die liebe'));
var_dump(isWanted('Über die Liebe', 'über die liebe'));
