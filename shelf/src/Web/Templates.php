<?php

declare(strict_types=1);

namespace Shelf\Web;

use Closure;
use LogicException;

/**
 * Shelf's templates: files in one folder that each return a function. The data is passed as
 * its arguments, by name (['title' => ...]) or, for a list, one item per argument, so a wrong
 * name or type stops with an error instead of an empty spot.
 */
final readonly class Templates
{
    public function __construct(private string $dir) {}

    /**
     * A whole page: the template $name inside the layout.
     *
     * @param array<mixed> $data the template's arguments
     * @param array<string, mixed> $frame more arguments for the layout, such as who is logged in (chapter 62)
     */
    public function page(string $title, string $name, array $data, array $frame = []): string
    {
        return $this->render('layout', ['title' => $title, 'content' => $this->render($name, $data), ...$frame]);
    }

    /** @param array<mixed> $data */
    public function render(string $name, array $data): string
    {
        if (preg_match('/^[a-z][a-z0-9-]*$/', $name) !== 1) {
            throw new LogicException("\"$name\" is not a template name");
        }
        $template = require "$this->dir/$name.php";
        if (!$template instanceof Closure) {
            throw new LogicException("templates/$name.php must return a function");
        }
        ob_start();
        try {
            $template(...$data);
        } finally {
            $html = (string) ob_get_clean();
        }

        return $html;
    }
}
