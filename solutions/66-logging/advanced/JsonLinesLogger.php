<?php

declare(strict_types=1);

// Exercise 66, advanced: a PSR-3 logger that writes JSON lines.

use Psr\Log\AbstractLogger;

final class JsonLinesLogger extends AbstractLogger
{
    public function __construct(private string $file, private DateTimeImmutable $time) {}

    /** @param array<mixed> $context */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $text = (string) $message;
        foreach ($context as $key => $value) {
            if (is_string($value) || is_int($value) || is_float($value)) {
                $text = str_replace('{' . $key . '}', (string) $value, $text);
            }
            if ($value instanceof Throwable) {
                $context[$key] = $value::class . ': ' . $value->getMessage();
            }
        }
        $line = ['time' => $this->time->format(DATE_ATOM), 'level' => $level, 'message' => $text, 'context' => $context];
        file_put_contents($this->file, json_encode($line, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    }
}
