<?php

declare(strict_types=1);

namespace Shelf\Web;

/** What a visitor asked for: the method, the path, and the fields of the query and the form, as strings. */
final readonly class Request
{
    /**
     * @param array<string, string> $query
     * @param array<string, string> $form
     */
    public function __construct(
        public string $method,
        public string $path,
        public array $query = [],
        public array $form = [],
    ) {}

    /** The request PHP received. Fields that arrive as arrays (name[]=x) are left out. */
    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        $uri = $_SERVER['REQUEST_URI'] ?? null;

        return new self(
            is_string($method) ? $method : 'GET',
            (string) parse_url(is_string($uri) ? $uri : '/', PHP_URL_PATH),
            self::strings($_GET),
            self::strings($_POST),
        );
    }

    /** A form field, trimmed, or '' if it wasn't sent. */
    public function field(string $name): string
    {
        return trim($this->form[$name] ?? '');
    }

    /**
     * @param array<mixed> $fields
     *
     * @return array<string, string>
     */
    private static function strings(array $fields): array
    {
        $strings = [];
        foreach ($fields as $name => $value) {
            if (is_string($value)) {
                $strings[(string) $name] = $value;
            }
        }

        return $strings;
    }
}
