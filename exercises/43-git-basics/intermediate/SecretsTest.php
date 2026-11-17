<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SecretsTest extends TestCase
{
    public function testCleanFilesPass(): void
    {
        require_once subject(__DIR__, 'secrets.php');

        self::assertSame([], findSecrets([
            'src/Mail.php' => "<?php\n\$key = getenv('API_KEY');\n\$password = \$_POST['password'] ?? '';\n",
            '.env.example' => "MAIL_API_KEY=\n",
            'config/app.php' => "<?php\nreturn ['token_length' => 32];\n",
            'README.md' => "Set API_KEY first.\n",
        ]));
    }

    public function testEnvironmentAndKeyFiles(): void
    {
        require_once subject(__DIR__, 'secrets.php');

        self::assertSame([
            'config/.env: an environment file',
            '.env.local: an environment file',
            'deploy/server.pem: a key file',
            'deploy/app.key: a key file',
        ], findSecrets([
            'config/.env' => '',
            '.env.local' => '',
            '.env.dist' => '',
            'deploy/server.pem' => '',
            'deploy/app.key' => '',
        ]));
    }

    public function testSecretsInTheContent(): void
    {
        require_once subject(__DIR__, 'secrets.php');

        self::assertSame([
            'notes.txt: a private key',
            'src/Mail.php line 3: a password or key',
            'src/Mail.php line 4: a password or key',
            'config.json line 2: a password or key',
            'deploy.sh line 1: a password or key',
        ], findSecrets([
            'notes.txt' => "-----BEGIN RSA PRIVATE KEY-----\nMIIEow...\n-----END RSA PRIVATE KEY-----\n",
            'src/Mail.php' => "<?php\n\n\$password = 'correct-horse';\nconst API_KEY = 'mk_4f9a1c7e2b8d';\n",
            'config.json' => "{\n    \"token\": \"abcdef123456\"\n}\n",
            'deploy.sh' => "DB_SECRET=s3cr3t-value ./deploy\n",
        ]));
    }

    public function testShortValuesAreNotSecrets(): void
    {
        require_once subject(__DIR__, 'secrets.php');

        self::assertSame([], findSecrets(['test.php' => "\$password = 'abc';\n"]));
    }
}
