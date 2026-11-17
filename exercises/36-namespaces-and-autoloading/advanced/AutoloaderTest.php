<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AutoloaderTest extends TestCase
{
    private static string $dir;

    public static function setUpBeforeClass(): void
    {
        self::$dir = sys_get_temp_dir() . '/autoload-' . bin2hex(random_bytes(4));
        mkdir(self::$dir . '/Storage', 0o777, true);
        file_put_contents(self::$dir . '/Book.php', "<?php\nnamespace Exercise36;\nfinal class Book {}\n");
        file_put_contents(self::$dir . '/Storage/Json.php', "<?php\nnamespace Exercise36\\Storage;\nfinal class Json {}\n");
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$dir . '/Storage/Json.php');
        unlink(self::$dir . '/Book.php');
        rmdir(self::$dir . '/Storage');
        rmdir(self::$dir);
    }

    public function testLoadsClassesWhenTheyAreFirstUsed(): void
    {
        require_once subject(__DIR__, 'autoloader.php');
        registerAutoloader('Exercise36\\', self::$dir);

        self::assertFalse(class_exists('Exercise36\Book', false), 'Nothing is loaded before it is used.');
        self::assertTrue(class_exists('Exercise36\Book'));
        self::assertTrue(class_exists('Exercise36\Storage\Json'));
    }

    public function testOtherClassesAreLeftAlone(): void
    {
        require_once subject(__DIR__, 'autoloader.php');
        registerAutoloader('Exercise36\\', self::$dir);

        self::assertFalse(class_exists('Exercise36\Missing'));
        self::assertFalse(class_exists('Elsewhere\Book'));
    }
}
