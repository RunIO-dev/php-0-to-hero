<?php

declare(strict_types=1);

// Email addresses have complicated rules. Don't write a regex for them: PHP has a check.
foreach (['amira@example.org', 'jonas.weber@mail.example.de', 'kenji@', 'ana example.org'] as $email) {
    $valid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    echo str_pad($email, 30), $valid ? 'valid' : 'invalid', "\n";
}
