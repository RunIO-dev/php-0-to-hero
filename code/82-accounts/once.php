<?php

declare(strict_types=1);

// A link that works once (chapter 82): a reset link carries a fingerprint of the password's hash.
// A new password makes a new hash, so the old link's fingerprint no longer matches, though its
// signature is still good. No table of used tokens is needed.

use Symfony\Component\HttpFoundation\UriSigner;

require __DIR__ . '/vendor/autoload.php';

function fingerprint(string $hash): string
{
    return substr(hash('sha256', $hash), 0, 16);
}

$signer = new UriSigner('a secret only the server knows');
$hash = password_hash('the old password, long enough', PASSWORD_DEFAULT);
$link = $signer->sign('https://shelf.example/reset?member=7&check=' . fingerprint($hash), new DateInterval('PT1H'));

$works = function (string $link) use ($signer, &$hash): string {
    parse_str((string) parse_url($link, PHP_URL_QUERY), $query);

    return $signer->check($link) && hash_equals(fingerprint($hash), $query['check']) ? 'works' : 'used up';
};

echo 'the link, first time: ', $works($link), "\n";
$hash = password_hash('a brand new set of words', PASSWORD_DEFAULT); // what the reset form stores
echo 'the link, again:      ', $works($link), "\n";
