<?php

declare(strict_types=1);

// The bundles Shelf uses (chapter 72), and in which environments. Flex adds a line for each bundle
// that composer require installs: Twig in chapter 73.

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
];
