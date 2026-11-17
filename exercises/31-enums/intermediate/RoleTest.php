<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function testTypedRoles(): void
    {
        require_once subject(__DIR__, 'role.php');

        self::assertSame(Role::Staff, roleFrom('  Staff '));
        self::assertSame(Role::Reader, roleFrom('reader'));
        self::assertSame(Role::Guest, roleFrom('GUEST'));
    }

    public function testAnUnknownRole(): void
    {
        require_once subject(__DIR__, 'role.php');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown role "admin". Use one of: reader, staff, guest');
        roleFrom('admin');
    }

    public function testTheListComesFromTheEnum(): void
    {
        $code = (string) file_get_contents(subject(__DIR__, 'role.php'));
        $function = substr($code, (int) strpos($code, 'function roleFrom'));

        self::assertStringContainsString('cases()', $function, 'Take the list of roles from Role::cases().');
    }
}
