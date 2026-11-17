<?php

declare(strict_types=1);

// Exercise 49, advanced: a View class for the templates in a folder (see templates/).

final readonly class View
{
    public function __construct(private string $dir) {}

    /** @param array<mixed> $data */
    public function render(string $name, array $data = []): string
    {
        if (preg_match('/^[a-z0-9-]+$/', $name) !== 1 || !is_file("$this->dir/$name.php")) {
            throw new InvalidArgumentException("No template named \"$name\"");
        }
        $template = require "$this->dir/$name.php";
        if (!$template instanceof Closure) {
            throw new LogicException("$name.php must return a function");
        }
        ob_start();
        try {
            $template(...$data);
        } finally {
            $html = (string) ob_get_clean();
        }

        return $html;
    }

    /** @param array<mixed> $data */
    public function page(string $title, string $name, array $data = []): string
    {
        return $this->render('layout', ['title' => $title, 'content' => $this->render($name, $data)]);
    }
}
