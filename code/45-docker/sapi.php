<?php

declare(strict_types=1);

// The same PHP can run a script in a terminal, or answer a web server. PHP_SAPI says which.

echo 'Running as: ', PHP_SAPI, "\n";
