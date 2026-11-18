<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'ResetLink.php');

final class ResetLinkTest extends TestCase
{
    private const int NOW = 1_795_000_000;

    private ResetLink $links;
    private string $hash;

    protected function setUp(): void
    {
        $this->links = new ResetLink('a secret only the server knows');
        $this->hash = password_hash('the old password, long enough', PASSWORD_BCRYPT, ['cost' => 4]);
    }

    public function testTheLinkHasItsParts(): void
    {
        $link = $this->links->make(7, $this->hash, self::NOW + 3600);

        self::assertMatchesRegularExpression('~^/reset\?member=7&check=[0-9a-f]{16}&expires=1795003600&sig=[0-9a-f]{64}$~', $link);
    }

    public function testALinkWorksForItsMemberWithinTheHour(): void
    {
        $link = $this->links->make(7, $this->hash, self::NOW + 3600);

        self::assertSame(7, $this->links->member($link, $this->hash, self::NOW));
        self::assertSame(7, $this->links->member($link, $this->hash, self::NOW + 3599));
        self::assertNull($this->links->member($link, $this->hash, self::NOW + 3600), 'expired');
    }

    public function testAChangedLinkIsRefused(): void
    {
        $link = $this->links->make(7, $this->hash, self::NOW + 3600);

        self::assertNull($this->links->member(str_replace('member=7', 'member=8', $link), $this->hash, self::NOW));
        self::assertNull($this->links->member(str_replace('expires=1795003600', 'expires=1895003600', $link), $this->hash, self::NOW));
        self::assertNull($this->links->member(substr($link, 0, -1) . 'x', $this->hash, self::NOW));
        self::assertNull($this->links->member(strstr($link, '&sig=', true) ?: '', $this->hash, self::NOW), 'no signature');
        self::assertNull(new ResetLink('another secret')->member($link, $this->hash, self::NOW));
    }

    public function testALinkIsUsedUpByANewPassword(): void
    {
        $link = $this->links->make(7, $this->hash, self::NOW + 3600);
        $new = password_hash('a brand new set of words', PASSWORD_BCRYPT, ['cost' => 4]);

        self::assertNull($this->links->member($link, $new, self::NOW));
    }
}
