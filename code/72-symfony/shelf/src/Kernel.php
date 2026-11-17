<?php

declare(strict_types=1);

namespace Shelf;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Shelf as a Symfony app (chapter 72). The kernel reads config/: the bundles it uses, their
 * settings, the services and the routes. MicroKernelTrait does the reading.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
