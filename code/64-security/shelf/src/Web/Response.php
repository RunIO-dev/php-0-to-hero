<?php

declare(strict_types=1);

namespace Shelf\Web;

/** What to answer: a status, headers and a body. Nothing is sent until send() is called. */
final readonly class Response
{
    /**
     * Sent with every page (chapter 64). Shelf has no JavaScript, so no script may run at all; styles,
     * images and forms only from Shelf itself; no other site may show it in a frame.
     */
    public const array SECURITY_HEADERS = [
        'Content-Security-Policy' => "default-src 'self'; script-src 'none'; frame-ancestors 'none'; form-action 'self'; base-uri 'none'",
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'same-origin',
    ];

    /** @param array<string, string> $headers */
    public function __construct(
        public int $status = 200,
        public array $headers = [],
        public string $body = '',
    ) {}

    public static function html(string $body, int $status = 200): self
    {
        return new self($status, ['Content-Type' => 'text/html; charset=UTF-8', ...self::SECURITY_HEADERS], $body);
    }

    /** Post/Redirect/Get: after a change, the browser asks for $path with GET. */
    public static function redirect(string $path): self
    {
        return new self(303, ['Location' => $path]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }
}
