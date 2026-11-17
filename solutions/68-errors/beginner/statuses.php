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
        'created' => 201,
        'removed, nothing to send' => 204,
        'body is not valid JSON' => 400,
        'not logged in' => 401,
        'logged in, but not allowed' => 403,
        'no such resource' => 404,
        'method not allowed' => 405,
        'already exists' => 409,
        'body is not JSON at all, but a form' => 415,
        'JSON with wrong data' => 422,
        'too many requests' => 429,
        default => throw new InvalidArgumentException("Unknown situation: $situation"),
    };
}
