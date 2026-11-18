<?php

declare(strict_types=1);

// Components (chapter 80): small templates with one job each, put together into a page.
// "include ... only" gives a component exactly the variables in "with"; "embed" also fills its blocks.

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/vendor/autoload.php';

$twig = new Environment(new FilesystemLoader(__DIR__ . '/templates'), ['strict_variables' => true]);

echo $twig->render('shelf.html.twig', [
    'heading' => 'Books',
    'books' => [
        ['title' => 'Kindred', 'copies' => 0],
        ['title' => 'Sula', 'copies' => 2],
    ],
]);
echo "-----\n";
echo $twig->render('shelf.html.twig', ['heading' => 'Books', 'books' => []]);
