<?php

declare(strict_types=1);

// ✗ The token in the address: /loans.php?token=8a57...

require_once __DIR__ . '/account.php';

$token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
$member = TOKENS[hash('sha256', $token)] ?? null;
