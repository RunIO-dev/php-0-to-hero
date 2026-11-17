<?php

declare(strict_types=1);

// The body has started, so it is too late for a header.

echo "Moved\n";

header('Location: /hello.php', true, 301);
