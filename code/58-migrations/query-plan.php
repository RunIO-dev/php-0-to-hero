<?php

declare(strict_types=1);

// EXPLAIN QUERY PLAN: how SQLite will find the rows. An index changes the answer.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');
$query = 'EXPLAIN QUERY PLAN SELECT due FROM loans WHERE member_id = 1';

show($db, $query);
show($db, 'CREATE INDEX loans_by_member ON loans (member_id)');
show($db, $query);
