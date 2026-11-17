<?php

declare(strict_types=1);

// Exercise 66, intermediate: keep secrets out of the log.
//
// safeContext($context) returns the context for a log message with every secret replaced by
// "[hidden]": the value of each key whose name contains "password", "token" or "secret", in any
// case (such as "new_password" or "CSRF_TOKEN"). Arrays inside the context are cleaned the same way.
// Everything else stays as it is.

/**
 * @param array<mixed> $context
 * @return array<mixed>
 */
function safeContext(array $context): array
{
    return $context;
}
// Check your work: composer exercise 66 intermediate
