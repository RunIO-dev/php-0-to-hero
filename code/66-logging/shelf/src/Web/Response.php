<?php

declare(strict_types=1);

namespace Shelf\Web;

use Psr\Log\LoggerInterface;
use Throwable;

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

    /**
     * The answer to an error that nothing caught (chapter 66): show little, log much. The log gets
     * everything; the visitor gets a short page with a reference that finds the line in the log, and
     * the details only on a developer's computer. Plain HTML, without templates: they may be what failed.
     */
    public static function error(Throwable $e, LoggerInterface $logger, bool $showDetails): self
    {
        $reference = bin2hex(random_bytes(4));
        $logger->error('Uncaught {class}, reference {reference}', ['class' => $e::class, 'reference' => $reference, 'exception' => $e]);
        $details = $showDetails ? "\n<pre>" . htmlspecialchars((string) $e, ENT_QUOTES) . '</pre>' : '';

        return self::html("<!doctype html>\n<title>Error · Shelf</title>\n<h1>Something went wrong</h1>\n"
            . "<p>It has been noted, with the reference $reference. Please try again later.</p>$details\n", 500);
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
