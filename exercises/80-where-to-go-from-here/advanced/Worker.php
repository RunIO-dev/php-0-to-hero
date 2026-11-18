<?php

declare(strict_types=1);

// A queue's worker (chapter 80). A queue delivers each message at least once: after a crash, the same
// message can come again. handle($message, $now) handles one delivery, and returns what happens next:
// - ["done", null]: the handler ran without an exception. A message whose id is done is never handled
//   again: the handler isn't called, and the answer is ["done", null].
// - ["retry", $at]: the handler threw a RuntimeException. The message comes again at $now plus the delay
//   for this try: the first failure waits $delays[0] seconds, the second $delays[1], and so on.
// - ["failed", null]: the handler threw, and every delay is used up; or it threw an
//   InvalidArgumentException, a message that can never work, which is not tried again. failures() keeps
//   each failed id with the exception's message, for a person to look at. A failed message that comes
//   again is not handled again, and stays failed.
// Ids are independent: one message's tries never change another's.

final readonly class Message
{
    public function __construct(
        public string $id,
        public string $body,
    ) {}
}

final class Worker
{
    /**
     * @param Closure(Message): void $handler
     * @param list<int> $delays seconds to wait after the first, second, ... failure
     */
    public function __construct(
        private readonly Closure $handler,
        private readonly array $delays = [10, 60, 300],
    ) {}

    /** @return array{'done'|'retry'|'failed', ?int} */
    public function handle(Message $message, int $now): array
    {
        ($this->handler)($message); // TODO: exceptions, retries, and messages that came before

        return ['done', null];
    }

    /** @return array<string, string> id => the exception's message */
    public function failures(): array
    {
        return []; // TODO
    }
}
