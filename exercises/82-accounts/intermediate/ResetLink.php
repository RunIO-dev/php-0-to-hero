<?php

declare(strict_types=1);

// A reset link signed by hand (chapter 82), as UriSigner does it, with PHP's own hash_hmac().
//   make(7, $hash, $expires) returns "/reset?member=7&check=…&expires=…&sig=…":
//   - check: the first 16 characters of hash('sha256', $hash), the member's current password hash;
//   - expires: the Unix time until which the link works;
//   - sig: hash_hmac('sha256', the address up to and without "&sig=", the secret).
//   member(string $link, string $currentHash, int $now) returns the member's number when the link is
//   exactly as made, not expired ($now < expires), and its check matches $currentHash; else null.
// Compare signatures with hash_equals(), never ===: it takes as long whether the first letter is
// wrong or the last, so nobody can guess a signature letter by letter.

final readonly class ResetLink
{
    public function __construct(#[\SensitiveParameter] private string $secret) {}

    public function make(int $member, string $hash, int $expires): string
    {
        return "/reset?member=$member"; // TODO
    }

    public function member(string $link, string $currentHash, int $now): ?int
    {
        return null; // TODO
    }
}
