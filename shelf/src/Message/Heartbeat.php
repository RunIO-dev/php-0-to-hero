<?php

declare(strict_types=1);

namespace Shelf\Message;

/** A message (chapter 88): "I am alive": every 5 minutes, so that the health check knows the worker runs. */
final readonly class Heartbeat {}
