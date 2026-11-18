<?php

declare(strict_types=1);

// Whether a name may be used to sign up (chapter 82): 2 to 40 characters; letters of any script,
// digits, spaces, dots, apostrophes and hyphens; the first one a letter or a digit. Spaces around
// it don't count: "  Ana  " is "Ana".
//   validName('Ana Lima')  === true
//   validName('Zoë O'Neil') === true
//   validName('أمل')       === true
//   validName('-Ana')      === false
//   validName('<b>Ana</b>') === false

function validName(string $name): bool
{
    return preg_match("/^[\\p{L}\\p{N}][\\p{L}\\p{N} .'-]{1,39}$/uD", trim($name)) === 1;
}
