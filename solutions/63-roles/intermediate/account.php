<?php

declare(strict_types=1);

// Exercise 63, intermediate: write allow().

session_start(['cookie_httponly' => true, 'cookie_secure' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);

/** As a database would hold them: Amira's password is "tea kettle under the lemon tree", Jonas's "river stones in the morning light". */
const MEMBERS = [
    'Amira' => ['role' => 'reader', 'hash' => '$2y$12$Et12/UhGgMQGbXUyhR4kye46uCcUZIYbl8wzKwu7nzJLYXnbinDsW'],
    'Jonas' => ['role' => 'staff', 'hash' => '$2y$12$isBbMN5VadhzFzBt0PRV0e5YAIfRcyvl.k4TuILQ8IDddHH1BZfru'],
];

function token(): string
{
    if (!is_string($_SESSION['csrf'] ?? null)) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function checkToken(): void
{
    if (!hash_equals(token(), is_string($_POST['_token'] ?? null) ? $_POST['_token'] : '')) {
        http_response_code(403);
        exit("Expired.\n");
    }
}

/** The name of the member who is logged in, or null. */
function member(): ?string
{
    return is_string($_SESSION['member'] ?? null) ? $_SESSION['member'] : null;
}

/** The role of the member who is logged in, or null for a visitor. */
function role(): ?string
{
    $member = member();

    return $member === null ? null : MEMBERS[$member]['role'] ?? null;
}

/** Stops the page unless a member with one of these roles is logged in: a visitor goes to the login. */
function allow(string ...$roles): void
{
    if (role() === null) {
        header('Location: /login.php', true, 303);
        exit;
    }
    if (!in_array(role(), $roles, true)) {
        http_response_code(403);
        exit("Not allowed.\n");
    }
}
