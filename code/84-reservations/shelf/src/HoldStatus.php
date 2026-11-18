<?php

declare(strict_types=1);

namespace Shelf;

/**
 * Where a reservation is (chapter 84). It waits in the book's queue until a copy comes back; then the
 * copy is set aside, ready, until the member collects it or the days to pick it up are over.
 */
enum HoldStatus: string
{
    case Waiting = 'waiting';
    case Ready = 'ready';
    case Collected = 'collected';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /** Still to be done: in the queue, or a copy waiting at the desk. */
    public function isOpen(): bool
    {
        return $this === self::Waiting || $this === self::Ready;
    }
}
