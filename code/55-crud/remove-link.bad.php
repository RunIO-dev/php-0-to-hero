<?php

declare(strict_types=1);

// ✗ A link that deletes: GET /remove-link.bad.php?id=3. Never change data on a GET.

$db = new PDO('sqlite:' . __DIR__ . '/../../shelf/var/shelf.sqlite');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$delete = $db->prepare('DELETE FROM books WHERE id = :id');
$delete->execute(['id' => $id]);
header('Location: /');
