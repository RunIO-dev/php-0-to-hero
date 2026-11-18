<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Dispatcher.php');

final class BookReturned
{
    /** @var list<string> */
    public array $seen = [];
}

final class LoanRequested implements Stoppable
{
    /** @var list<string> */
    public array $seen = [];
    public ?string $refusal = null;

    public function isStopped(): bool
    {
        return $this->refusal !== null;
    }
}

final class DispatcherTest extends TestCase
{
    /** A listener that notes its name in the event. */
    private static function note(string $name): Closure
    {
        return static function (BookReturned|LoanRequested $event) use ($name): void {
            $event->seen[] = $name;
        };
    }

    public function testTheHighestPriorityRunsFirst(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->listen(BookReturned::class, self::note('log'));
        $dispatcher->listen(BookReturned::class, self::note('stock'), 10);
        $dispatcher->listen(BookReturned::class, self::note('mail'), -5);

        self::assertSame(['stock', 'log', 'mail'], $dispatcher->dispatch(new BookReturned())->seen);
    }

    public function testTheSamePriorityKeepsTheOrderAdded(): void
    {
        $dispatcher = new Dispatcher();
        foreach (['first', 'second', 'third'] as $name) {
            $dispatcher->listen(BookReturned::class, self::note($name), 5);
        }
        $dispatcher->listen(BookReturned::class, self::note('late'));

        self::assertSame(['first', 'second', 'third', 'late'], $dispatcher->dispatch(new BookReturned())->seen);
    }

    public function testAnEventWithoutListenersComesBackUnchanged(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->listen(LoanRequested::class, self::note('check'));
        $event = new BookReturned();

        self::assertSame($event, $dispatcher->dispatch($event));
        self::assertSame([], $event->seen);
    }

    public function testAStoppedEventSkipsTheRest(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->listen(LoanRequested::class, self::note('lend'));
        $dispatcher->listen(LoanRequested::class, static function (LoanRequested $e): void {
            $e->seen[] = 'limit';
            $e->refusal = 'Amira already has five books.';
        }, 10);
        $dispatcher->listen(LoanRequested::class, self::note('blocked'), 20);

        $event = $dispatcher->dispatch(new LoanRequested());
        self::assertSame(['blocked', 'limit'], $event->seen);
        self::assertSame('Amira already has five books.', $event->refusal);
    }

    public function testAnEventStoppedBeforeItIsSentReachesNoListener(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->listen(LoanRequested::class, self::note('lend'));
        $event = new LoanRequested();
        $event->refusal = 'The library is closed.';

        self::assertSame([], $dispatcher->dispatch($event)->seen);
    }

    public function testAnEventThatCanStopRunsEveryListenerWhileItGoesOn(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->listen(LoanRequested::class, self::note('limit'), 10);
        $dispatcher->listen(LoanRequested::class, self::note('lend'));

        self::assertSame(['limit', 'lend'], $dispatcher->dispatch(new LoanRequested())->seen);
    }
}
