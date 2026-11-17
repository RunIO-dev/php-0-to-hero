<?php

declare(strict_types=1);

// ✗ The loans first, then each loan's book, one query at a time.

use Library\Loan;

require_once __DIR__ . '/entities.php';

$entities = entities();
foreach ($entities->getRepository(Loan::class)->findAll() as $loan) {
    echo $loan->book->title, "\n"; // a query for each loan's book: 1 + N queries
}
