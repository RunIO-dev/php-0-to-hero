<?php

declare(strict_types=1);

// Exercise 66, intermediate: keep secrets out of the log.

/**
 * @param array<mixed> $context
 * @return array<mixed>
 */
function safeContext(array $context): array
{
    $safe = [];
    foreach ($context as $key => $value) {
        if (preg_match('/password|token|secret/i', (string) $key) === 1) {
            $safe[$key] = '[hidden]';
        } else {
            $safe[$key] = is_array($value) ? safeContext($value) : $value;
        }
    }

    return $safe;
}
