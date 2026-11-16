<?php

declare(strict_types=1);

// One function, one place to decide what "the same title" means.
function sameTitle(string $a, string $b): bool
{
    return mb_strtolower(trim($a)) === mb_strtolower(trim($b));
}

var_dump(sameTitle('Über die Liebe', 'über die liebe'));
var_dump(sameTitle('Über die Liebe ', 'über die liebe'));
var_dump(sameTitle('Kindred', 'Kitchen'));
