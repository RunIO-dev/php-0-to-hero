<?php

declare(strict_types=1);

// Exercise 51, intermediate: one token per form.

require_once __DIR__ . '/Session.php';

final readonly class Tokens
{
    public function __construct(private Session $session) {}

    public function token(string $form): string
    {
        $token = $this->session->get("csrf.$form");
        if ($token === null) {
            $token = bin2hex(random_bytes(32));
            $this->session->set("csrf.$form", $token);
        }

        return $token;
    }

    public function use(string $form, string $sent): bool
    {
        $token = $this->session->get("csrf.$form");
        if ($token === null || !hash_equals($token, $sent)) {
            return false;
        }
        $this->session->pull("csrf.$form");

        return true;
    }
}
