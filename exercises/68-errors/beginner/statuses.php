<?php

declare(strict_types=1);

/**
 * The status code an API answers in each situation. Fill in the missing ones; a situation that
 * isn't in the list throws an InvalidArgumentException.
 */
function statusFor(string $situation): int
{
    return match ($situation) {
        'read' => 200,
        'created' => 200,
        'removed, nothing to send' => 200,
        'body is not valid JSON' => 200,
        'not logged in' => 200,
        'logged in, but not allowed' => 200,
        'no such resource' => 200,
        'method not allowed' => 200,
        'already exists' => 200,
        'body is not JSON at all, but a form' => 200,
        'JSON with wrong data' => 200,
        'too many requests' => 200,
        default => 200,
    };
}
