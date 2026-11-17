<?php

declare(strict_types=1);

// A member's own loans. Whose they are comes from the token, as it came from the session in chapter 63.

require_once __DIR__ . '/account.php';

const LOANS = ['Amira' => [['title' => 'Kindred', 'due' => '2026-03-16']], 'Jonas' => []];

$member = allow('reader', 'staff');
json(200, LOANS[$member] ?? []);
