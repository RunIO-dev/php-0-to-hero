<?php

declare(strict_types=1);

namespace Shelf\Report;

/** How many loans came back in a time, and how many of them late (chapter 86). */
final readonly class Returns
{
    public function __construct(public int $returned, public int $late) {}

    /** The share back on time, as a whole percent; null when nothing came back, rather than 100 or 0. */
    public function onTimePercent(): ?int
    {
        return $this->returned === 0 ? null : (int) round(100 * ($this->returned - $this->late) / $this->returned);
    }
}
