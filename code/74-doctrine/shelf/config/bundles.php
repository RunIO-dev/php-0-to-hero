<?php

declare(strict_types=1);

// The bundles Shelf uses (chapter 72), and in which environments. Flex adds a line for each bundle
// that composer require installs: Twig in chapter 73, Doctrine and its migrations in 74.

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
];
