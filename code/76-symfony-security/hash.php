<?php

declare(strict_types=1);

// Symfony's password hashers (symfony/password-hasher): password_hash() of chapter 61, with a name
// for the choice, "auto", and a way to tell when a stored hash is too weak by today's settings.

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

require __DIR__ . '/vendor/autoload.php';

$hashers = new PasswordHasherFactory(['member' => ['algorithm' => 'auto', 'cost' => 12]]);
$hasher = $hashers->getPasswordHasher('member');

$hash = $hasher->hash('tea kettle under the lemon tree');
echo substr($hash, 0, 7), "…\n";                                     // bcrypt, cost 12
var_dump($hasher->verify($hash, 'tea kettle under the lemon tree'));
var_dump($hasher->verify($hash, 'tea kettle'));

// A hash from chapter 61, made with a lower cost: still accepted, but worth making again.
$old = password_hash('river stones in the morning light', PASSWORD_BCRYPT, ['cost' => 10]);
var_dump($hasher->verify($old, 'river stones in the morning light'));
var_dump($hasher->needsRehash($old));
