<?php

declare(strict_types=1);

namespace Shelf\Web;

/** What to answer: a status, headers and a body. Nothing is sent until send() is called. */
final readonly class Response
{
    /** @param array<string, string> $headers */
    public function __construct(
        public int $status = 200,
        public array $headers = [],
        public string $body = '',
    ) {}

    public static function html(string $body, int $status = 200): self
    {
        return new self($status, ['Content-Type' => 'text/html; charset=UTF-8'], $body);
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
