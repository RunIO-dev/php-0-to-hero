<?php

declare(strict_types=1);

// The answer of a health check (chapter 88), for a monitor. $checks names each check and gives it
// as a function that returns true (it works) or false. A check that throws counts as false. Return
// the HTTP status, 200 when every check passed and 503 otherwise, and the body, as JSON:
//   {"status": "ok", "checks": {"database": "ok", "worker": "failing"}}
// with "status" "failing" when any check failed. A thrown message never goes into the body: it may
// name a path, a user or a server, and anyone may ask.

/**
 * @param array<string, Closure(): bool> $checks
 * @return array{int, string}
 */
function health(array $checks): array
{
    $answers = [];
    foreach ($checks as $name => $check) {
        try {
            $answers[$name] = $check() ? 'ok' : 'failing';
        } catch (Throwable) {
            $answers[$name] = 'failing';
        }
    }
    $ok = !in_array('failing', $answers, true);

    return [$ok ? 200 : 503, json_encode(['status' => $ok ? 'ok' : 'failing', 'checks' => $answers], JSON_THROW_ON_ERROR)];
}
