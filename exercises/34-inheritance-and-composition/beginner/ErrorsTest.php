<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ErrorsTest extends TestCase
{
    public function testTheFamily(): void
    {
        require_once subject(__DIR__, 'errors.php');

        self::assertTrue(is_subclass_of(LibraryException::class, RuntimeException::class));
        self::assertTrue(is_subclass_of(MemberNotFound::class, LibraryException::class));
        self::assertTrue(is_subclass_of(CardExpired::class, LibraryException::class));
        self::assertTrue((new ReflectionClass(MemberNotFound::class))->isFinal(), 'No class may extend MemberNotFound.');
        self::assertTrue((new ReflectionClass(CardExpired::class))->isFinal(), 'No class may extend CardExpired.');
    }

    public function testOneCatchForBoth(): void
    {
        require_once subject(__DIR__, 'errors.php');

        $caught = [];
        foreach ([['', false], ['Amira', true]] as [$name, $expired]) {
            try {
                checkCard($name, $expired);
            } catch (LibraryException $e) {
                $caught[] = $e::class;
            }
        }

        self::assertSame(['MemberNotFound', 'CardExpired'], $caught);
    }

    public function testAValidCard(): void
    {
        require_once subject(__DIR__, 'errors.php');

        checkCard('Jonas', false);
        $this->addToAssertionCount(1);
    }
}
