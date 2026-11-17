<?php

declare(strict_types=1);

// Exercise 66, advanced: a PSR-3 logger that writes JSON lines.
//
// Each message adds one line to the file: a JSON object with, in this order,
//   "time":    the time given to the constructor, as DATE_ATOM (2026-03-16T10:00:00+00:00)
//   "level":   the level, such as "warning"
//   "message": the message, with each {key} that has a string or number in the context filled in
//   "context": the whole context; an exception in it becomes its class and message, "Class: message"
// Encode with JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE, and end each line with "\n".
// json_encode escapes line breaks, so one message is always one line. Programs read such logs easily.

use Psr\Log\AbstractLogger;

final class JsonLinesLogger extends AbstractLogger
{
    public function __construct(private string $file, private DateTimeImmutable $time) {}

    /** @param array<mixed> $context */
    public function log($level, string|Stringable $message, array $context = []): void {}
}
// Check your work: composer exercise 66 advanced
