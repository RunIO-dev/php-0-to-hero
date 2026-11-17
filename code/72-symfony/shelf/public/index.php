<?php

declare(strict_types=1);

// Shelf on the web: the front controller (chapter 72). Every request comes here, as in chapter 48;
// Symfony's Runtime reads .env, makes the kernel, and lets it answer.

use Shelf\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return static function (array $context): Kernel {
    /** @var array{APP_ENV: string, APP_DEBUG: string} $context the settings from .env and the environment */
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
