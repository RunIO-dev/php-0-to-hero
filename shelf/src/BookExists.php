<?php

declare(strict_types=1);

namespace Shelf;

/**
 * A new book that is already on the shelf (chapter 71). It is still an InvalidArgumentException, so
 * the pages that show the message keep working; the API answers it with 409 Conflict.
 */
final class BookExists extends Refused {}
