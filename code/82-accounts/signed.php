<?php

declare(strict_types=1);

// A signed link (chapter 82): UriSigner adds an expiry time and a hash of the whole address, made
// with a secret only the server knows. Whoever changes one letter, or waits too long, has a link
// that no longer checks out. The clock is a fixed one here, so the example prints the same every time.

use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpFoundation\Exception\SignedUriException;
use Symfony\Component\HttpFoundation\UriSigner;

require __DIR__ . '/vendor/autoload.php';

$clock = new MockClock('2026-11-20 10:00:00 UTC');
$signer = new UriSigner('a secret only the server knows', clock: $clock);

$link = $signer->sign('https://shelf.example/confirm?member=7&email=ana%40example.org', new DateInterval('P2D'));
echo $link, "\n\n";

$check = function (string $name, string $url) use ($signer): void {
    try {
        $signer->verify($url);
        echo "$name: valid\n";
    } catch (SignedUriException $e) {
        echo "$name: refused, ", new ReflectionClass($e)->getShortName(), "\n";
    }
};
$check('as sent', $link);
$check('another address', str_replace('ana%40', 'eve%40', $link));
$check('no signature', 'https://shelf.example/confirm?member=7&email=eve%40example.org');
$clock->modify('+2 days +1 minute');
$check('two days later', $link);
