<?php

declare(strict_types=1);

// Exercise 44, intermediate: pages that moved. A solution.

const MOVED = ['list' => '/books.php', 'about' => '/about.php', 'join' => '/members/join.php'];

$page = $_GET['page'] ?? '';
if (isset(MOVED[$page])) {
    header('Location: ' . MOVED[$page], true, 301);
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo "Not found\n";
