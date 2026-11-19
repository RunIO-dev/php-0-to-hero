<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use Shelf\Backups;
use Shelf\Heartbeat;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Filesystem\Filesystem;

/** /api/health (chapter 88): what a monitor asks every few minutes. */
final class HealthTest extends ShelfWebTestCase
{
    use ClockSensitiveTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::mockTime('2026-03-16 10:00');
        $data = $_SERVER['SHELF_DATA'] ?? null; // from .env.test
        self::assertIsString($data);
        new Filesystem()->remove($this->client->getKernel()->getProjectDir() . "/$data");
        new Filesystem()->mkdir($this->client->getKernel()->getProjectDir() . "/$data");
    }

    public function testAWorkerThatRanAndABackupMeanOk(): void
    {
        self::service(Heartbeat::class)->beat(new DateTimeImmutable('2026-03-16 09:55'));
        self::service(Backups::class)->make(new DateTimeImmutable('2026-03-16 02:30'));

        self::assertSame(['status' => 'ok', 'checks' => ['database' => 'ok', 'worker' => 'ok', 'backup' => 'ok']], $this->health());
        self::assertResponseStatusCodeSame(200);
        self::assertResponseHeaderSame('Cache-Control', 'no-store, private');
    }

    public function testAStoppedWorkerFails(): void
    {
        self::service(Heartbeat::class)->beat(new DateTimeImmutable('2026-03-16 09:40'));
        self::service(Backups::class)->make(new DateTimeImmutable('2026-03-16 02:30'));

        self::assertSame(['status' => 'failing', 'checks' => ['database' => 'ok', 'worker' => 'failing', 'backup' => 'ok']], $this->health());
        self::assertResponseStatusCodeSame(503);
    }

    public function testNoBackupFails(): void
    {
        self::service(Heartbeat::class)->beat(new DateTimeImmutable('2026-03-16 09:58'));
        self::service(Backups::class)->make(new DateTimeImmutable('2026-03-15 02:30')); // 31 hours ago

        self::assertSame('failing', $this->health()['checks']['backup']);
    }

    /** @return array{status: string, checks: array<string, string>} */
    private function health(): array
    {
        $this->client->request('GET', '/api/health');
        /** @var array{status: string, checks: array<string, string>} */
        return json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }
}
