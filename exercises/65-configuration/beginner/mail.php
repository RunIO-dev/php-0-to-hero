<?php

declare(strict_types=1);

// Exercise 65, beginner: a secret from the environment.
//
// mailPassword() returns the password for the library's mail server. It is in the code, so it is in
// Git. Read it from the environment variable MAIL_PASSWORD instead. If that isn't set, or is empty,
// throw a RuntimeException with the message "MAIL_PASSWORD is not set".

function mailPassword(): string
{
    return 'sunflower-2026';
}
// Check your work: composer exercise 65 beginner
