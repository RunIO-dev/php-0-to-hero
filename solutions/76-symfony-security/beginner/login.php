<?php

declare(strict_types=1);

// Checking a password at login with Symfony's hashers, and making its hash stronger on the way.
// - memberHasher(): a hasher with algorithm "auto" and cost 12.
// - logIn($hasher, $storedHash, $typed): ['ok' => whether $typed is the password, 'newHash' => a new
//   hash when the password was right and the stored hash is weaker than the hasher's settings, else null].
//   A wrong password never gets a new hash.

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

function memberHasher(): PasswordHasherInterface
{
    return new PasswordHasherFactory(['member' => ['algorithm' => 'auto', 'cost' => 12]])->getPasswordHasher('member');
}

/** @return array{ok: bool, newHash: ?string} */
function logIn(PasswordHasherInterface $hasher, string $storedHash, string $typed): array
{
    if (!$hasher->verify($storedHash, $typed)) {
        return ['ok' => false, 'newHash' => null];
    }

    return ['ok' => true, 'newHash' => $hasher->needsRehash($storedHash) ? $hasher->hash($typed) : null];
}
