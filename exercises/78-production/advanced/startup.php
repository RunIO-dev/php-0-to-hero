<?php

declare(strict_types=1);

// Before Shelf starts on a server (chapter 78), check the settings the server gave it. missingSettings($env)
// takes the environment variables, as $_SERVER holds them, and returns every problem, in this order:
//   "APP_ENV must be prod"
//   "APP_DEBUG must be 0"                           (only when it is set: missing means 0 in prod)
//   "APP_SECRET is missing"  or  "APP_SECRET must be at least 32 characters"
//                            or  "APP_SECRET is the one from .env.dev"   (DEV_SECRET below)
//   "DATABASE_URL is missing"  or  "DATABASE_URL must start with sqlite:, mysql: or mariadb:"
//   "SHELF_HOST must be the site's domain"         (missing, localhost, or an IP address like 127.0.0.1)
// Values are strings; a value with only spaces counts as missing.

const DEV_SECRET = '0588f0a89e5eba9056f16d7e3c09a2a4';

/**
 * @param array<string, mixed> $env
 * @return list<string>
 */
function missingSettings(array $env): array
{
    return []; // TODO
}
