<?php

declare(strict_types=1);

namespace Shelf;

/**
 * Sign-up with an address that already has an account (chapter 82). The page must not say so: that
 * would tell anyone whether an address is a member's. The controller writes to the address instead.
 */
final class EmailInUse extends ShelfException {}
