<?php

declare(strict_types=1);

// Removing a book changes the library: staff only, checked here, on the server, before anything happens.

require_once __DIR__ . '/account.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}
allow('staff');
checkToken();

header('Content-Type: text/plain; charset=UTF-8');
echo 'Removed ', is_string($_POST['title'] ?? null) ? $_POST['title'] : '', "\n";
