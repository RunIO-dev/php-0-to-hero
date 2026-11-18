<?php

declare(strict_types=1);

namespace Shelf\Message;

/**
 * A message (chapter 85): "send the reminders that are due now". It holds nothing: the handler finds
 * what is due when it runs. Shelf's schedule sends it every hour.
 */
final readonly class SendReminders {}
