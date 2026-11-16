<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MembersTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = (string) tempnam(sys_get_temp_dir(), 'members-');
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function testReadsEveryMember(): void
    {
        require_once subject(__DIR__, 'members.php');
        file_put_contents($this->path, "name,email\nAmira,amira@example.org\n\"Ana, Jr.\",ana@example.org\n");

        self::assertSame(
            [['name' => 'Amira', 'email' => 'amira@example.org'], ['name' => 'Ana, Jr.', 'email' => 'ana@example.org']],
            loadMembers($this->path),
        );
    }

    public function testOnlyAHeader(): void
    {
        require_once subject(__DIR__, 'members.php');
        file_put_contents($this->path, "name,email\n");

        self::assertSame([], loadMembers($this->path));
    }

    public function testAMissingFileThrows(): void
    {
        require_once subject(__DIR__, 'members.php');
        unlink($this->path);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($this->path);
        loadMembers($this->path);
    }
}
