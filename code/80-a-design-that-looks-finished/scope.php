<?php

declare(strict_types=1);

// What a component sees (chapter 80). Without "only", an include sees every variable of the page:
// the badge below takes the page's "level", "danger", although nobody gave it one.

use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/vendor/autoload.php';

$files = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($files, ['strict_variables' => true]);
echo $twig->render('scope.html.twig', ['level' => 'danger']);

// With "only", the badge sees nothing it wasn't given: the missing level is an error at once.
$only = new ArrayLoader(['only.html.twig' => "{% include '_badge.html.twig' with {text: 'Available'} only %}"]);
$twig = new Environment(new ChainLoader([$only, $files]), ['strict_variables' => true]);
try {
    echo $twig->render('only.html.twig', ['level' => 'danger']);
} catch (RuntimeError $e) {
    echo 'With only: ', $e->getRawMessage(), "\n";
}
