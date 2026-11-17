<?php

declare(strict_types=1);

// ✗ The whole form goes into the log, password and all.

use Psr\Log\LoggerInterface;

function logLogin(LoggerInterface $log): void
{
    $log->info('Login attempt', $_POST);
}
