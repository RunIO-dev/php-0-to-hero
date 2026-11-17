<?php

declare(strict_types=1);

// The logout of chapter 62.

require_once __DIR__ . '/account.php';

checkToken();
unset($_SESSION['member']);
session_regenerate_id(true);
header('Location: /books.php', true, 303);
