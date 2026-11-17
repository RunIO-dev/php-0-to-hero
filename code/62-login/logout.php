<?php

declare(strict_types=1);

// Logging out: a POST with the token, never a link. The session forgets the member, and gets a new ID.

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
