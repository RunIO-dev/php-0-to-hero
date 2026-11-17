<?php

declare(strict_types=1);

// The same check, asked through the built-in web server.

header('Content-Type: text/plain; charset=UTF-8');

echo 'Running as: ', PHP_SAPI, "\n";
