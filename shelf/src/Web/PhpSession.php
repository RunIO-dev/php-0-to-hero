<?php

declare(strict_types=1);

namespace Shelf\Web;

use RuntimeException;

/**
 * PHP's own session, with safe settings. It starts when a value is saved, or when the browser
 * sent a session cookie: a visitor who only reads gets no cookie.
 */
final class PhpSession implements Session
{
    private const array SETTINGS = [
        'name' => 'shelf',
        'cookie_httponly' => true,   // no JavaScript can read the ID
        'cookie_secure' => true,     // only over HTTPS, and on localhost
        'cookie_samesite' => 'Lax',  // not sent with another site's forms
        'use_strict_mode' => true,   // only IDs this server created
    ];

    public function get(string $key): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !isset($_COOKIE[self::SETTINGS['name']])) {
            return null;
        }
        $this->start();
        $value = $_SESSION[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    public function set(string $key, string $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function pull(string $key): ?string
    {
        $value = $this->get($key);
        unset($_SESSION[$key]);

        return $value;
    }

    public function regenerate(): void
    {
        $this->start();
        if (!session_regenerate_id(true)) { // true: the old ID's data is deleted, so it is worth nothing
            throw new RuntimeException('The session could not get a new ID');
        }
    }

    private function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !session_start(self::SETTINGS)) {
            throw new RuntimeException('The session could not be started');
        }
    }
}
