<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../databases.php';
require_once subject(__DIR__, 'connect.php');

final class ConnectTest extends TestCase
{
    public function testItConnectsAsTheApplicationsUser(): void
    {
        $db = appConnection();

        self::assertSame('mysql', $db->getAttribute(PDO::ATTR_DRIVER_NAME));
        self::assertSame(
            [['user' => 'shelf_app@%', 'db' => 'shelf', 'charset' => 'utf8mb4']],
            rowsOf($db, 'SELECT CURRENT_USER() AS user, DATABASE() AS db, @@character_set_connection AS charset'),
        );
    }

    public function testErrorsAreExceptionsAndStatementsArePrepared(): void
    {
        $db = appConnection();

        self::assertSame(PDO::ERRMODE_EXCEPTION, $db->getAttribute(PDO::ATTR_ERRMODE));
        self::assertFalse($db->getAttribute(PDO::ATTR_EMULATE_PREPARES));
    }

    public function testThePasswordIsNotInTheCode(): void
    {
        self::assertNotContains((string) getenv('SHELF_APP_PASSWORD'), strings_in(subject(__DIR__, 'connect.php')));
    }

    public function testAMissingPasswordIsNamed(): void
    {
        $password = (string) getenv('SHELF_APP_PASSWORD');
        putenv('SHELF_APP_PASSWORD');
        try {
            appConnection();
            self::fail('Without the variable, appConnection() must throw');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('SHELF_APP_PASSWORD', $e->getMessage());
        } finally {
            putenv("SHELF_APP_PASSWORD=$password");
        }
    }
}
