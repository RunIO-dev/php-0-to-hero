<?php

declare(strict_types=1);

// Exercise 65, beginner: a secret from the environment.

function mailPassword(): string
{
    $password = getenv('MAIL_PASSWORD');
    if ($password === false || $password === '') {
        throw new RuntimeException('MAIL_PASSWORD is not set');
    }

    return $password;
}
