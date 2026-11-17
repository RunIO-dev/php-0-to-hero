<?php

declare(strict_types=1);

// ✗ The button is shown only to staff, so the page "needs no check". Anyone can send the form.

require_once __DIR__ . '/account.php';

checkToken();

header('Content-Type: text/plain; charset=UTF-8');
echo 'Removed ', is_string($_POST['title'] ?? null) ? $_POST['title'] : '', "\n";
