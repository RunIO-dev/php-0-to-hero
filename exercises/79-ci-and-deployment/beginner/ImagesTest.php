<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'images.php');

final class ImagesTest extends TestCase
{
    private const string COMMIT = '8f3c2e1a9b0d4c5e6f7a8b9c0d1e2f3a4b5c6d7e';

    public function testTheNameOfAnImage(): void
    {
        self::assertSame('ghcr.io/runio-dev/shelf-app:' . self::COMMIT, imageName('RunIO-dev/shelf', 'app', self::COMMIT));
        self::assertSame('ghcr.io/amira/my.shelf_2-web:' . self::COMMIT, imageName('amira/my.shelf_2', 'web', strtoupper(self::COMMIT)));
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function mistakes(): iterable
    {
        yield 'no owner' => ['shelf', 'app', self::COMMIT];
        yield 'a path' => ['runio-dev/shelf/app', 'app', self::COMMIT];
        yield 'a space' => ['runio-dev/my shelf', 'app', self::COMMIT];
        yield 'another stage' => ['runio-dev/shelf', 'test', self::COMMIT];
        yield 'a branch' => ['runio-dev/shelf', 'app', 'main'];
        yield 'a short commit' => ['runio-dev/shelf', 'app', substr(self::COMMIT, 0, 7)];
        yield 'not hexadecimal' => ['runio-dev/shelf', 'app', str_repeat('g', 40)];
        yield 'a command' => ['runio-dev/shelf', 'app', self::COMMIT . ';rm'];
    }

    #[DataProvider('mistakes')]
    public function testABadValueIsRefused(string $repository, string $target, string $commit): void
    {
        $this->expectException(InvalidArgumentException::class);
        imageName($repository, $target, $commit);
    }
}
