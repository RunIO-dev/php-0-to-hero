<?php

declare(strict_types=1);

namespace Shelf;

/** A reservation number that a form sent, with no reservation of this member behind it (chapter 84). */
final class HoldNotFound extends ShelfException {}
