<?php

declare(strict_types=1);

// A small event dispatcher (chapter 89), as Symfony's works inside:
// - listen($eventClass, $listener, $priority): a listener for the events of exactly that class;
// - dispatch($event) calls the listeners of its class, the highest priority first, and listeners with the
//   same priority in the order they were added; it returns the event, which listeners may have changed;
// - an event that implements Stoppable stops the rest of the listeners once isStopped() is true, checked
//   before each listener.

interface Stoppable
{
    public function isStopped(): bool;
}

final class Dispatcher
{
    /** @var array<class-string, list<callable(object): void>> */
    private array $listeners = [];

    /**
     * @param class-string $event
     * @param callable(object): void $listener
     */
    public function listen(string $event, callable $listener, int $priority = 0): void
    {
        $this->listeners[$event][] = $listener; // TODO: the priority
    }

    /**
     * @template T of object
     * @param T $event
     * @return T
     */
    public function dispatch(object $event): object
    {
        foreach ($this->listeners[$event::class] ?? [] as $listener) {
            $listener($event); // TODO: the order, and stopping
        }

        return $event;
    }
}
