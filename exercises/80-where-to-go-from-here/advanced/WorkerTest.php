<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Worker.php');

final class WorkerTest extends TestCase
{
    /** @var list<string> the ids the handler was called with */
    private array $calls = [];

    /** A handler that fails with $exception for the first $failures calls of each id, then works. */
    private function handler(int $failures, ?Exception $exception = null): Closure
    {
        $seen = [];

        return function (Message $message) use (&$seen, $failures, $exception): void {
            $this->calls[] = $message->id;
            $seen[$message->id] = ($seen[$message->id] ?? 0) + 1;
            if ($seen[$message->id] <= $failures) {
                throw $exception ?? new RuntimeException('The mail server does not answer.');
            }
        };
    }

    public function testAMessageThatWorksIsDone(): void
    {
        $worker = new Worker($this->handler(0));

        self::assertSame(['done', null], $worker->handle(new Message('m1', 'Remind Amira of Kindred'), 1000));
        self::assertSame(['m1'], $this->calls);
        self::assertSame([], $worker->failures());
    }

    public function testADoneMessageThatComesAgainIsNotHandledAgain(): void
    {
        $worker = new Worker($this->handler(0));
        $worker->handle(new Message('m1', 'Remind Amira of Kindred'), 1000);

        self::assertSame(['done', null], $worker->handle(new Message('m1', 'Remind Amira of Kindred'), 1005));
        self::assertSame(['m1'], $this->calls, 'Amira gets one reminder, not two');
    }

    public function testAFailureWaitsLongerEachTimeThenFails(): void
    {
        $worker = new Worker($this->handler(10));
        $message = new Message('m1', 'Remind Amira of Kindred');

        self::assertSame(['retry', 1010], $worker->handle($message, 1000));
        self::assertSame(['retry', 1070], $worker->handle($message, 1010));
        self::assertSame(['retry', 1370], $worker->handle($message, 1070));
        self::assertSame(['failed', null], $worker->handle($message, 1370));
        self::assertSame(['m1' => 'The mail server does not answer.'], $worker->failures());
        self::assertCount(4, $this->calls);
    }

    public function testAFailedMessageThatComesAgainStaysFailed(): void
    {
        $worker = new Worker($this->handler(10), []);
        $message = new Message('m1', 'Remind Amira of Kindred');

        self::assertSame(['failed', null], $worker->handle($message, 1000));
        self::assertSame(['failed', null], $worker->handle($message, 2000));
        self::assertSame(['m1'], $this->calls);
    }

    public function testASecondTryCanWork(): void
    {
        $worker = new Worker($this->handler(1), [30]);
        $message = new Message('m1', 'Remind Amira of Kindred');

        self::assertSame(['retry', 1030], $worker->handle($message, 1000));
        self::assertSame(['done', null], $worker->handle($message, 1030));
        self::assertSame([], $worker->failures());
    }

    public function testAMessageThatCanNeverWorkIsNotTriedAgain(): void
    {
        $worker = new Worker($this->handler(10, new InvalidArgumentException('No member "Zed".')));

        self::assertSame(['failed', null], $worker->handle(new Message('m1', 'Remind Zed of Kindred'), 1000));
        self::assertSame(['m1' => 'No member "Zed".'], $worker->failures());
        self::assertSame(['m1'], $this->calls);
    }

    public function testEachMessageHasItsOwnTries(): void
    {
        $worker = new Worker($this->handler(1), [10, 60]);
        $amira = new Message('m1', 'Remind Amira of Kindred');
        $jonas = new Message('m2', 'Remind Jonas of Beloved');

        self::assertSame(['retry', 1010], $worker->handle($amira, 1000));
        self::assertSame(['retry', 1015], $worker->handle($jonas, 1005));
        self::assertSame(['done', null], $worker->handle($amira, 1010));
        self::assertSame(['done', null], $worker->handle($jonas, 1015));
    }
}
