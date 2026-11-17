<?php

declare(strict_types=1);

// Routing on the whole REQUEST_URI: the query string is part of it.

header('Content-Type: text/plain; charset=UTF-8');

echo match ($_SERVER['REQUEST_URI']) {
    '/books' => "Kindred\nKitchen\n",
    default => "Not found\n",
};
