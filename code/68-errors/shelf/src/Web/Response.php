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

    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

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
     * An answer for programs (chapter 67): $data as JSON, readable, with slashes and letters as they are.
     * nosniff keeps a browser from ever taking it as a page.
     */
    public static function json(mixed $data, int $status = 200): self
    {
        $body = json_encode($data, self::JSON_FLAGS);

        return new self($status, ['Content-Type' => 'application/json', 'X-Content-Type-Options' => 'nosniff'], "$body\n");
    }

    /**
     * An error, for programs (chapter 68): problem details, as RFC 9457 describes them. Without a $type
     * of its own, the problem is just the status: "about:blank", with the status's usual name as $title.
     *
     * @param array<string, mixed> $more more members, such as "errors"
     */
    public static function problem(int $status, string $title, string $detail, string $type = 'about:blank', array $more = []): self
    {
        $body = json_encode(['type' => $type, 'title' => $title, 'status' => $status, 'detail' => $detail, ...$more], self::JSON_FLAGS);

        return new self($status, ['Content-Type' => 'application/problem+json', 'X-Content-Type-Options' => 'nosniff'], "$body\n");
    }

    /**
     * The answer to an error that nothing caught (chapter 66): show little, log much. The log gets
     * everything; the visitor gets a short page with a reference that finds the line in the log, and
     * the details only on a developer's computer. Plain HTML, without templates: they may be what failed.
     * A program that asked the API gets the same as problem details (chapter 68).
     */
    public static function error(Throwable $e, LoggerInterface $logger, bool $showDetails, bool $api = false): self
    {
        $reference = bin2hex(random_bytes(4));
        $logger->error('Uncaught {class}, reference {reference}', ['class' => $e::class, 'reference' => $reference, 'exception' => $e]);
        if ($api) {
            $detail = "Something went wrong. It has been noted, with the reference $reference.";

            return self::problem(500, 'Internal Server Error', $detail, more: ['reference' => $reference, ...$showDetails ? ['exception' => (string) $e] : []]);
        }
        $details = $showDetails ? "\n<pre>" . htmlspecialchars((string) $e, ENT_QUOTES) . '</pre>' : '';

        return self::html("<!doctype html>\n<title>Error · Shelf</title>\n<h1>Something went wrong</h1>\n"
            . "<p>It has been noted, with the reference $reference. Please try again later.</p>$details\n", 500);
    }

    /** Post/Redirect/Get: after a change, the browser asks for $path with GET. */
    public static function redirect(string $path): self
    {
        return new self(303, ['Location' => $path]);
    }

    /** The same answer with one more header (chapter 68). */
    public function withHeader(string $name, string $value): self
    {
        return new self($this->status, [...$this->headers, $name => $value], $this->body);
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
