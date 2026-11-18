<?php

declare(strict_types=1);

// When to try a failed message again (chapter 85), as Messenger's retry_strategy does.
//   new Backoff(delay: 60, multiplier: 2, maxDelay: 3600, maxTries: 3)
//   delay($try): seconds to wait after try $try (1, 2, ...) failed: delay * multiplier^($try - 1),
//                never more than maxDelay. A $try below 1 is an InvalidArgumentException.
//   retryAt($failedAt, $try): the moment to try again, or null once maxTries tries have failed.
//   schedule(): every delay, in order, until the message gives up: [60, 120] for the example.

final readonly class Backoff
{
    public function __construct(
        public int $delay = 60,
        public int $multiplier = 2,
        public int $maxDelay = 3600,
        public int $maxTries = 3,
    ) {}

    public function delay(int $try): int
    {
        return 0; // TODO
    }

    public function retryAt(DateTimeImmutable $failedAt, int $try): ?DateTimeImmutable
    {
        return null; // TODO
    }

    /** @return list<int> */
    public function schedule(): array
    {
        return []; // TODO
    }
}
