<?php

declare(strict_types=1);

// What this example's pages share: the session, the members' password hashes, the CSRF token
// (chapter 51), and who is logged in.

session_start(['cookie_httponly' => true, 'cookie_secure' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);

/** Name => password_hash('tea kettle under the lemon tree', PASSWORD_DEFAULT), as a database would hold it. */
const MEMBERS = ['Amira' => '$2y$12$Et12/UhGgMQGbXUyhR4kye46uCcUZIYbl8wzKwu7nzJLYXnbinDsW'];

function token(): string
{
    if (!is_string($_SESSION['csrf'] ?? null)) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

/** Stops a POST that doesn't send the session's token back. */
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
