<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'redact.php');

final class RedactTest extends TestCase
{
    public function testSecretsAreHidden(): void
    {
        $form = ['name' => 'Amira', 'password' => 'tea kettle under the lemon tree', '_TOKEN' => 'abc123', 'client_secret' => 's3'];

        self::assertSame(['name' => 'Amira', 'password' => '[hidden]', '_TOKEN' => '[hidden]', 'client_secret' => '[hidden]'], safeContext($form));
    }

    public function testInsideArraysToo(): void
    {
        $context = ['path' => '/login', 'form' => ['name' => 'Amira', 'New_Password' => 'x'], 'tries' => 3];

        self::assertSame(['path' => '/login', 'form' => ['name' => 'Amira', 'New_Password' => '[hidden]'], 'tries' => 3], safeContext($context));
    }
}
