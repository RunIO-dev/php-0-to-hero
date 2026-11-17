<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'errors.php');

final class ErrorsTest extends TestCase
{
    /** @return iterable<string, array{int, ?string}> */
    public static function codes(): iterable
    {
        yield 'ok' => [UPLOAD_ERR_OK, null];
        yield 'too large for PHP' => [UPLOAD_ERR_INI_SIZE, 'The file is too large.'];
        yield 'too large for the form' => [UPLOAD_ERR_FORM_SIZE, 'The file is too large.'];
        yield 'no file' => [UPLOAD_ERR_NO_FILE, 'Please choose a file.'];
        yield 'cut off' => [UPLOAD_ERR_PARTIAL, 'The upload was cut off. Please try again.'];
        yield 'no temporary folder' => [UPLOAD_ERR_NO_TMP_DIR, 'The upload failed.'];
        yield 'disk full' => [UPLOAD_ERR_CANT_WRITE, 'The upload failed.'];
    }

    #[DataProvider('codes')]
    public function testEveryCodeHasItsMessage(int $error, ?string $message): void
    {
        self::assertSame($message, uploadProblem($error));
    }
}
