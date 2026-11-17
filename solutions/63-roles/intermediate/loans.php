<?php

declare(strict_types=1);

// Each member sees their own loans. Whose they are comes from the session, never from the request.

require_once __DIR__ . '/account.php';

const LOANS = ['Amira' => ['Kindred, due 2026-03-16'], 'Jonas' => ['Sula, due 2026-03-30']];

allow('reader', 'staff');
header('Content-Type: text/plain; charset=UTF-8');

$member = (string) member();
echo "Loans of $member:\n";
foreach (LOANS[$member] ?? [] as $loan) {
    echo "- $loan\n";
}
