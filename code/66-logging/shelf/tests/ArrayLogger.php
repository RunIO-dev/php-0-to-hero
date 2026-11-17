<?php

declare(strict_types=1);

namespace Shelf\Tests;

use Psr\Log\AbstractLogger;
use Stringable;

/** A logger for tests (chapter 66): it keeps each message, with its placeholders filled in. */
final class ArrayLogger extends AbstractLogger
{
    /** @var list<string> "level message" */
    public array $lines = [];

    /** @param array<mixed> $context */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $text = (string) $message;
        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value instanceof Stringable) {
                $text = str_replace('{' . $key . '}', (string) $value, $text);
            }
        }
        $this->lines[] = (is_string($level) ? $level : 'unknown') . " $text";
    }
}
