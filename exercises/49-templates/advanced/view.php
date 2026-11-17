<?php

declare(strict_types=1);

// Exercise 49, advanced: a View class for the templates in a folder (see templates/).
//
// render($name, $data): load "$dir/$name.php", call the function it returns with $data spread
// into its arguments, and return what it printed.
// - A name that isn't made of lowercase letters, digits and dashes (such as '../secret' or ''):
//   InvalidArgumentException. So is a name without a file.
// - A file that doesn't return a function (Closure): LogicException.
// - If the template throws, the exception goes on to the caller, and the output buffer is
//   closed: ob_get_level() is the same as before render() was called.
// page($title, $name, $data): render $name, then 'layout' with title and content.

final readonly class View
{
    public function __construct(private string $dir) {}

    /** @param array<mixed> $data */
    public function render(string $name, array $data = []): string
    {
        return '';
    }

    /** @param array<mixed> $data */
    public function page(string $title, string $name, array $data = []): string
    {
        return '';
    }
}
// Check your work: composer exercise 49 advanced
