<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'flash.php');

final class FlashTest extends TestCase
{
    private ArraySession $session;
    private Flash $flash;

    protected function setUp(): void
    {
        $this->session = new ArraySession();
        $this->flash = new Flash($this->session);
    }

    public function testMessagesComeBackInOrderOnce(): void
    {
        $this->flash->add('success', 'Added "Sula".');
        $this->flash->add('error', 'The cover was too big.');

        self::assertSame([
            ['type' => 'success', 'text' => 'Added "Sula".'],
            ['type' => 'error', 'text' => 'The cover was too big.'],
        ], $this->flash->all());
        self::assertSame([], $this->flash->all());
    }

    public function testTheMessagesAreStoredAsJsonInTheSession(): void
    {
        $this->flash->add('success', 'Saved.');

        self::assertSame([['type' => 'success', 'text' => 'Saved.']], json_decode($this->session->values['flash'] ?? '', true));
    }

    public function testAnUnknownTypeIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->flash->add('warning', 'Careful.');
    }

    /** @return iterable<string, array{string}> */
    public static function broken(): iterable
    {
        yield 'not JSON' => ['{oops'];
        yield 'not a list' => ['{"type":"success","text":"x"}'];
        yield 'a wrong item' => ['[{"type":"success"}]'];
        yield 'a wrong type' => ['[{"type":"<script>","text":"x"}]'];
    }

    #[DataProvider('broken')]
    public function testABrokenSessionValueGivesNoMessages(string $value): void
    {
        $this->session->values['flash'] = $value;

        self::assertSame([], $this->flash->all());
    }
}
