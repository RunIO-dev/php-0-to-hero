<?php

declare(strict_types=1);

// The same error with display_errors on, as on a developer's computer: every detail, for everyone.

ini_set('display_errors', '1');

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->query('SELECT * FROM books');
