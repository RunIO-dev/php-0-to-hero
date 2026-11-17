<?php

declare(strict_types=1);

// Exercise 47, advanced: the rules of a sign-up form, which register.php uses.
//
// 1. values($post): the four fields name, email, year and terms as trimmed strings. A field that
//    is missing, or that arrives as an array (name[]=x is a valid request too), becomes ''.
//    Nothing else from $post is kept.
// 2. errors($values): one message per wrong field, with the field's name as key, and [] if all is
//    well. Report every wrong field at once, not only the first:
//    - name:  1 to 50 characters (count characters, not bytes)  → 'From 1 to 50 characters.'
//    - email: a valid email address                              → 'A valid email address, please.'
//    - year:  a whole number from 1900 to 2026                   → 'A year from 1900 to 2026.'
//    - terms: exactly 'yes'                                      → 'Please accept the rules.'

/**
 * @param array<mixed> $post
 *
 * @return array{name: string, email: string, year: string, terms: string}
 */
function values(array $post): array
{
    return ['name' => '', 'email' => '', 'year' => '', 'terms' => ''];
}

/**
 * @param array{name: string, email: string, year: string, terms: string} $values
 *
 * @return array<string, string>
 */
function errors(array $values): array
{
    return [];
}
// Check your work: composer exercise 47 advanced
