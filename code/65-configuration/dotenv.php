<?php

declare(strict_types=1);

// The settings in a .env file.

require_once __DIR__ . '/envfile.php';

var_dump(readEnvFile(__DIR__ . '/library.env'));
