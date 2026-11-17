<?php

declare(strict_types=1);

// Exercise 50, intermediate: a reading list in the session.

header('Content-Type: text/plain; charset=UTF-8');

session_start(['cookie_httponly' => true, 'cookie_secure' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);

$saved = $_SESSION['list'] ?? [];
$list = is_array($saved) ? array_values(array_filter($saved, 'is_string')) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = is_string($_POST['title'] ?? null) ? trim($_POST['title']) : '';
    if ($title !== '' && !in_array($title, $list, true)) {
        if (count($list) >= 3) {
            http_response_code(422);
            echo "Your list is full.\n";
            exit;
        }
        $list[] = $title;
        $_SESSION['list'] = $list;
    }
    header('Location: /list.php', true, 303);
    exit;
}

echo count($list), " on your list\n";
foreach ($list as $title) {
    echo $title, "\n";
}
