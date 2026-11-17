<?php

declare(strict_types=1);

namespace Shelf\Web;

/**
 * Protection against cross-site request forgery: a random token per session, which every form
 * sends back in the hidden field _token. Other sites can make a browser send a form here, but
 * can't read the token from our pages.
 */
final readonly class Csrf
{
    public const string FIELD = '_token';

    public function __construct(private Session $session) {}

    public function token(): string
    {
        $token = $this->session->get('csrf');
        if ($token === null) {
            $token = bin2hex(random_bytes(32));
            $this->session->set('csrf', $token);
        }

        return $token;
    }

    public function isValid(string $sent): bool
    {
        $token = $this->session->get('csrf');

        return $token !== null && hash_equals($token, $sent);
    }
}
