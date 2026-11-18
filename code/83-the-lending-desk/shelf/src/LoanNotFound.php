<?php

declare(strict_types=1);

namespace Shelf;

/** A loan number that a form sent, with no loan behind it (chapter 83). */
final class LoanNotFound extends ShelfException {}
