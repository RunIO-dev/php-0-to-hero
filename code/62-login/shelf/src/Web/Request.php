<?php

declare(strict_types=1);

namespace Shelf\Web;

/** What a visitor asked for: the method, the path, and the fields of the query and the form, as strings. */
final readonly class Request
{
    /**
     * @param array<string, string> $query
     * @param array<string, string> $form
     * @param array<string, Upload> $files
     */
    public function __construct(
        public string $method,
        public string $path,
        public array $query = [],
        public array $form = [],
        public array $files = [],
    ) {}

    /**
     * The request PHP received. Fields that arrive as arrays (name[]=x) are left out, and so are
     * files that PHP didn't receive as uploads in this request.
     */
    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        $uri = $_SERVER['REQUEST_URI'] ?? null;

        return new self(
            is_string($method) ? $method : 'GET',
            (string) parse_url(is_string($uri) ? $uri : '/', PHP_URL_PATH),
            self::strings($_GET),
            self::strings($_POST),
            self::uploads($_FILES),
        );
    }

    /** A query field, trimmed, or '' if it wasn't sent. */
    public function query(string $name): string
    {
        return trim($this->query[$name] ?? '');
    }

    /** A form field exactly as it was sent, or '': for a password, where every space counts (chapter 62). */
    public function secret(string $name): string
    {
        return $this->form[$name] ?? '';
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

    /**
     * @param array<mixed> $files PHP's $_FILES
     *
     * @return array<string, Upload>
     */
    private static function uploads(array $files): array
    {
        $uploads = [];
        foreach ($files as $name => $file) {
            if (!is_array($file) || !is_int($file['error'] ?? null) || !is_string($file['tmp_name'] ?? null)) {
                continue; // several files under one name (cover[]): not accepted
            }
            if ($file['error'] === UPLOAD_ERR_OK && !is_uploaded_file($file['tmp_name'])) {
                continue;
            }
            $uploads[(string) $name] = new Upload($file['error'], $file['error'] === UPLOAD_ERR_OK ? $file['tmp_name'] : '');
        }

        return $uploads;
    }
}
