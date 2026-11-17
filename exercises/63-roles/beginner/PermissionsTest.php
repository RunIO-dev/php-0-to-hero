<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'permissions.php');

final class PermissionsTest extends TestCase
{
    public function testEveryoneMaySeeTheBooks(): void
    {
        self::assertTrue(mayDo(null, 'see books'));
        self::assertTrue(mayDo('reader', 'see books'));
        self::assertTrue(mayDo('staff', 'see books'));
    }

    public function testMembersMayBorrow(): void
    {
        self::assertFalse(mayDo(null, 'borrow'));
        self::assertTrue(mayDo('reader', 'borrow'));
        self::assertTrue(mayDo('staff', 'borrow'));
    }

    public function testOnlyStaffMayChangeTheLibrary(): void
    {
        foreach (['add book', 'remove book', 'see all loans'] as $action) {
            self::assertFalse(mayDo(null, $action), $action);
            self::assertFalse(mayDo('reader', $action), $action);
            self::assertTrue(mayDo('staff', $action), $action);
        }
    }

    public function testWhatHasNoRuleIsRefused(): void
    {
        self::assertFalse(mayDo('staff', 'delete database'));
        self::assertFalse(mayDo('admin', 'see books'), 'An unknown role gets nothing');
    }
}
