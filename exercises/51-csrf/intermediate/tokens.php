<?php

declare(strict_types=1);

// Exercise 51, intermediate: one token per form.
//
// A token that works for every form is enough for most sites. Here, each form gets its own, so a
// token that leaked from one form (say, in a page cached by mistake) can't be used for another.
// - token($form): the token for that form name, 32 random bytes as hex, kept in the session under
//   "csrf.$form" and the same until it is used.
// - use($form, $sent): true if $sent is that form's token (hash_equals). A valid token is used up:
//   it is removed, so the next token($form) makes a new one, and the same token never works twice.
//   A wrong token leaves the stored one as it is.

require_once __DIR__ . '/Session.php';

final readonly class Tokens
{
    public function __construct(private Session $session) {}

    public function token(string $form): string
    {
        return '';
    }

    public function use(string $form, string $sent): bool
    {
        return true;
    }
}
// Check your work: composer exercise 51 intermediate
