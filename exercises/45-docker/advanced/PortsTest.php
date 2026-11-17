<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PortsTest extends TestCase
{
    #[DataProvider('mappings')]
    public function testWhoCanConnect(string $mapping, string $answer): void
    {
        require_once subject(__DIR__, 'ports.php');

        self::assertSame($answer, exposure($mapping));
    }

    /** @return array<string, array{string, string}> */
    public static function mappings(): array
    {
        return [
            'local' => ['127.0.0.1:8000:8000', 'only this computer, on port 8000'],
            'local IPv6' => ['[::1]:8080:80', 'only this computer, on port 8080'],
            'no address' => ['8000:8000', 'every network, on port 8000'],
            'every address' => ['0.0.0.0:8000:8000', 'every network, on port 8000'],
            'another port' => ['127.0.0.1:8080:8000', 'only this computer, on port 8080'],
        ];
    }

    #[DataProvider('wrong')]
    public function testRejectsWhatIsNotAMapping(string $mapping): void
    {
        require_once subject(__DIR__, 'ports.php');

        $this->expectException(InvalidArgumentException::class);
        exposure($mapping);
    }

    /** @return array<string, array{string}> */
    public static function wrong(): array
    {
        return ['one number' => ['8000'], 'letters' => ['web:8000'], 'too big' => ['99999:8000'], 'empty' => ['']];
    }
}
