<?php

declare(strict_types=1);

// A name the template uses, which the page didn't pass: with strict_variables, Twig stops, instead
// of printing nothing where the ISBN should be.

use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/vendor/autoload.php';

$twig = new Environment(new FilesystemLoader(__DIR__ . '/templates'), ['strict_variables' => true]);

try {
    echo $twig->render('book.html.twig', ['book' => ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979]]);
} catch (RuntimeError $e) {
    echo $e->getMessage(), "\n";
}
