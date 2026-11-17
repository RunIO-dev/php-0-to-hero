<?php

declare(strict_types=1);

namespace Shelf;

// A file that declares a class, and also does something when it is loaded.

final class Greeting
{
    public function text(): string
    {
        return 'Welcome to the library';
    }
}

echo (new Greeting())->text(), "\n"; // runs whenever the autoloader loads Greeting
