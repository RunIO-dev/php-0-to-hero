<?php

declare(strict_types=1);

// ✗ Two different messages tell a stranger which names have an account.

require_once __DIR__ . '/account.php';

$name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

if (!isset(MEMBERS[$name])) {
    exit("There is no member named $name.\n");
}
if (!password_verify($password, MEMBERS[$name])) {
    exit("Wrong password for $name.\n");
}
