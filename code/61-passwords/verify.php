<?php

declare(strict_types=1);

// password_verify: does a typed password belong to a stored hash?

$stored = password_hash('tea kettle under the lemon tree', PASSWORD_DEFAULT);

foreach (['tea kettle under the lemon tree', 'Tea kettle under the lemon tree', 'tea kettle', ''] as $typed) {
    echo password_verify($typed, $stored) ? 'yes' : 'no ', " \"$typed\"\n";
}
