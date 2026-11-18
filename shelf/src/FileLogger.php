<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Psr\Log\AbstractLogger;
use RuntimeException;
use Stringable;
use Throwable;

/**
 * Shelf's log (chapter 66): a PSR-3 logger that adds one line per message to a file. The time, the
 * level, the message with its {placeholders} filled in, and the rest of the context as JSON. Control
 * characters are escaped, so a line break in a visitor's input can't start a line of its own.
 */
final class FileLogger extends AbstractLogger
{
    /** @param ?DateTimeImmutable $now a fixed time for the tests; null: the time of each message */
    public function __construct(private string $file, private ?DateTimeImmutable $now = null) {}

    /** @param array<mixed> $context */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        [$text, $rest] = self::fill((string) $message, $context);
        $line = ($this->now ?? new DateTimeImmutable())->format(DATE_ATOM) . ' ' . strtoupper(is_string($level) ? $level : 'unknown') . " $text";
        if ($rest !== []) {
            $line .= ' ' . json_encode($rest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
        // Chapter 78: a stream such as php://stderr (in a container) has no folder, and can't be locked.
        $isStream = str_contains($this->file, '://');
        if (!$isStream && !is_dir(dirname($this->file)) && !mkdir(dirname($this->file), 0o755, true) && !is_dir(dirname($this->file))) {
            throw new RuntimeException('Could not make the folder for the log: ' . dirname($this->file));
        }
        file_put_contents($this->file, addcslashes($line, "\0..\37") . "\n", $isStream ? FILE_APPEND : FILE_APPEND | LOCK_EX);
    }

    /**
     * The message with the placeholders that have a value filled in, and the context that is left.
     *
     * @param array<mixed> $context
     * @return array{string, array<mixed>}
     */
    private static function fill(string $text, array $context): array
    {
        $rest = [];
        foreach ($context as $key => $value) {
            if (str_contains($text, '{' . $key . '}') && (is_scalar($value) || $value instanceof Stringable)) {
                $text = str_replace('{' . $key . '}', (string) $value, $text);
            } elseif ($value instanceof Throwable) {
                $rest[$key] = $value::class . ': ' . $value->getMessage() . ' at ' . $value->getFile() . ':' . $value->getLine();
            } else {
                $rest[$key] = $value;
            }
        }

        return [$text, $rest];
    }
}
