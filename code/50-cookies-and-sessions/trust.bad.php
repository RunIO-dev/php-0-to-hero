<?php

declare(strict_types=1);

// Who someone is, kept in a plain cookie: the visitor can change it.

header('Content-Type: text/plain; charset=UTF-8');

$role = $_COOKIE['role'] ?? 'reader';
echo $role === 'admin' ? "Welcome, admin. You may delete books.\n" : "Welcome, reader.\n";
