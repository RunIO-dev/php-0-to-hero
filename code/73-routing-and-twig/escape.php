<?php

declare(strict_types=1);

// Twig (twig/twig): templates in their own language, which escapes every value for HTML on its own.

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/vendor/autoload.php';

$twig = new Environment(new FilesystemLoader(__DIR__ . '/templates'), ['strict_variables' => true]);

echo $twig->render('book.html.twig', [
    'book' => ['title' => 'Kitchen <script>alert(1)</script>', 'author' => 'Banana Yoshimoto', 'year' => 1988, 'isbn' => null, 'copies' => 1],
]);
