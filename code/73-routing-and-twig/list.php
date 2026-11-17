<?php

declare(strict_types=1);

// A page that extends the layout, with a loop and filters.

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/vendor/autoload.php';

$twig = new Environment(new FilesystemLoader(__DIR__ . '/templates'), ['strict_variables' => true]);

echo $twig->render('list.html.twig', [
    'author' => 'Octavia E. Butler',
    'books' => [
        ['title' => 'Kindred', 'year' => 1979],
        ['title' => 'Parable of the Sower', 'year' => 1993],
    ],
]);
