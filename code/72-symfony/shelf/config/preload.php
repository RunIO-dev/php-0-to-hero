<?php

declare(strict_types=1);

// For production servers that preload classes (opcache.preload, Part 12): the list the cache warmer wrote.

foreach (glob(dirname(__DIR__) . '/var/cache/prod/*.preload.php') ?: [] as $file) {
    require $file;
}
