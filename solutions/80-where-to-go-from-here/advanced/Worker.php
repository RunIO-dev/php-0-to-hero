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
    /** @var array<string, true> */
    private array $done = [];
    /** @var array<string, int> failures so far, per id */
    private array $tries = [];
    /** @var array<string, string> */
    private array $failures = [];

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
        $id = $message->id;
        if (isset($this->done[$id])) {
            return ['done', null];
        }
        if (isset($this->failures[$id])) {
            return ['failed', null];
        }
        try {
            ($this->handler)($message);
        } catch (InvalidArgumentException $e) {
            return $this->fail($id, $e);
        } catch (RuntimeException $e) {
            $tries = $this->tries[$id] ?? 0;
            if (!isset($this->delays[$tries])) {
                return $this->fail($id, $e);
            }
            $this->tries[$id] = $tries + 1;

            return ['retry', $now + $this->delays[$tries]];
        }
        $this->done[$id] = true;
        unset($this->tries[$id]);

        return ['done', null];
    }

    /** @return array<string, string> id => the exception's message */
    public function failures(): array
    {
        return $this->failures;
    }

    /** @return array{'failed', null} */
    private function fail(string $id, Exception $e): array
    {
        $this->failures[$id] = $e->getMessage();
        unset($this->tries[$id]);

        return ['failed', null];
    }
}
