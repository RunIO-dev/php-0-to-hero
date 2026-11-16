<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AddTypesTest extends TestCase
{
    public function testEveryParameterHasAType(): void
    {
        require_once subject(__DIR__, 'add-types.php');

        foreach (['pagesLeft', 'isFinished'] as $name) {
            foreach ((new ReflectionFunction($name))->getParameters() as $parameter) {
                self::assertSame('int', (string) $parameter->getType(), "$name(): give \${$parameter->getName()} a type");
            }
        }
    }

    public function testBothFunctionsHaveAReturnType(): void
    {
        require_once subject(__DIR__, 'add-types.php');

        self::assertSame('int', (string) (new ReflectionFunction('pagesLeft'))->getReturnType(), 'pagesLeft(): add a return type');
        self::assertSame('bool', (string) (new ReflectionFunction('isFinished'))->getReturnType(), 'isFinished(): add a return type');
    }

    public function testTheyStillWork(): void
    {
        require_once subject(__DIR__, 'add-types.php');

        self::assertSame(120, pagesLeft(320, 200));
        self::assertFalse(isFinished(320, 200));
        self::assertTrue(isFinished(320, 320));
    }
}
