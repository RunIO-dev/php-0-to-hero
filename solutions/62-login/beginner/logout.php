<?php

declare(strict_types=1);

// Exercise 62, beginner: log out.

require_once __DIR__ . '/account.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}
checkToken();
unset($_SESSION['member']);
session_regenerate_id(true);
header('Location: /home.php', true, 303);
