<?php

declare(strict_types=1);

// Inside a container, localhost is that container, not the database's.

$db = new PDO('mysql:host=localhost;dbname=shelf;charset=utf8mb4', 'shelf_app', (string) getenv('SHELF_APP_PASSWORD'));
