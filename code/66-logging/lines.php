<?php

declare(strict_types=1);

// A PSR-3 logger that writes one line per message: the time, the level, the message with its
// placeholders filled in, and the rest of the context as JSON. This chapter's examples write to the
// screen, so you can see the lines; a real application writes to a file.

use Psr\Log\AbstractLogger;

final class LineLogger extends AbstractLogger
{
    /** @param resource $stream where the lines go */
    public function __construct(private $stream, private DateTimeImmutable $now) {}

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $text = (string) $message;
        $rest = [];
        foreach ($context as $key => $value) {
            if (str_contains($text, '{' . $key . '}') && (is_scalar($value) || $value instanceof Stringable)) {
                $text = str_replace('{' . $key . '}', (string) $value, $text);
            } else {
                $rest[$key] = $value instanceof Throwable ? $value::class . ': ' . $value->getMessage() : $value;
            }
        }
        $line = $this->now->format(DATE_ATOM) . ' ' . strtoupper(is_string($level) ? $level : 'unknown') . " $text";
        if ($rest !== []) {
            $line .= ' ' . json_encode($rest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        fwrite($this->stream, addcslashes($line, "\0..\37") . "\n"); // a line break in a value can't start a line of its own
    }
}
