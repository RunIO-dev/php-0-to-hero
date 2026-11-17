<?php

declare(strict_types=1);

// A transaction that is never finished: the next one can't start.

require_once __DIR__ . '/state.php';

$db = library();
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

$db->beginTransaction();
$db->exec("UPDATE books SET copies = 0 WHERE title = 'Sula'");
// ... an early return or a caught exception here, without commit() or rollBack()

$db->beginTransaction();
