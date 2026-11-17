<?php

declare(strict_types=1);

// ✗ Whose loans to show comes from the address, so any member can read anyone's.

require_once __DIR__ . '/account.php';

const LOANS = ['Amira' => ['Kindred, due 2026-03-16'], 'Jonas' => ['Sula, due 2026-03-30']];

allow('reader', 'staff');
header('Content-Type: text/plain; charset=UTF-8');

$member = is_string($_GET['member'] ?? null) ? $_GET['member'] : '';
echo "Loans of $member:\n";
foreach (LOANS[$member] ?? [] as $loan) {
    echo "- $loan\n";
}
