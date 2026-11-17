<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tools\Examples;
use Tools\Run;
use Tools\Web;

/** Web examples: served by PHP's built-in server, with the requests of their .http file. */
final class WebTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/web-' . bin2hex(random_bytes(4)) . '/code/46-web';
        mkdir($this->dir, 0o777, true);
    }

    protected function tearDown(): void
    {
        Run::remove(dirname($this->dir, 2));
    }

    public function testRequestsAndResponsesLikeCurl(): void
    {
        file_put_contents("$this->dir/hello.php", "<?php\necho 'Hello, ', htmlspecialchars(\$_GET['name'] ?? 'you'), \"!\\n\";\n");
        file_put_contents("$this->dir/hello.http", "GET /hello.php?name=%3Cb%3EAmira\n");

        self::assertSame(
            "> GET /hello.php?name=%3Cb%3EAmira\n\n< HTTP/1.1 200 OK\n< Content-type: text/html; charset=UTF-8\n\nHello, &lt;b&gt;Amira!\n",
            Examples::output(dirname($this->dir, 2), "$this->dir/hello.php"),
        );
        self::assertSame('php -S localhost:8000', Examples::command("$this->dir/hello.php")['line']);
    }

    public function testPostRedirectCookiesAndCapturedTokens(): void
    {
        file_put_contents("$this->dir/form.php", <<<'PHP'
            <?php
            session_start();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (($_POST['_token'] ?? '') !== ($_SESSION['token'] ?? null)) {
                    http_response_code(403);
                    exit("Bad token\n");
                }
                header('Location: /form.php', true, 303);
                exit;
            }
            $_SESSION['token'] = bin2hex(random_bytes(16));
            echo '<input name="_token" value="', $_SESSION['token'], '">', "\n";
            PHP);
        file_put_contents("$this->dir/form.http", <<<'HTTP'
            # capture token /value="([0-9a-f]+)"/
            GET /form.php

            ###
            POST /form.php
            Content-Type: application/x-www-form-urlencoded

            _token=wrong

            ###
            POST /form.php
            Content-Type: application/x-www-form-urlencoded

            _token={{token}}
            HTTP);
        $out = Web::output("$this->dir/form.php");

        self::assertStringContainsString('< Set-Cookie: PHPSESSID=…; path=/', $out);
        self::assertStringContainsString("<input name=\"_token\" value=\"{{token}}\">\n", $out);
        self::assertStringContainsString("> _token=wrong\n\n< HTTP/1.1 403 Forbidden", $out);
        self::assertStringContainsString("> _token={{token}}\n\n< HTTP/1.1 303 See Other", $out);
        self::assertStringContainsString('< Location: /form.php', $out);
        self::assertSame($out, Web::output("$this->dir/form.php"), 'the same every time, despite the random token');
    }

    public function testServedPagesAreNotRunOnTheirOwn(): void
    {
        file_put_contents("$this->dir/list.php", "<?php\necho \"list\\n\";\n");
        file_put_contents("$this->dir/add.php", "<?php\necho \"added\\n\";\n");
        file_put_contents("$this->dir/list.http", "GET /list.php\n\n###\nPOST /add.php\n");
        file_put_contents("$this->dir/list.out", Web::output("$this->dir/list.php"));

        self::assertSame(["$this->dir/list.php", "$this->dir/add.php"], Web::served($this->dir));
        self::assertSame([], Examples::check(dirname($this->dir, 2)), 'add.php needs no .out: list.http requests it');
    }

    public function testAFolderAndARouter(): void
    {
        mkdir("$this->dir/public");
        file_put_contents("$this->dir/public/index.php", "<?php\necho 'You asked for ', \$_SERVER['REQUEST_URI'], \"\\n\";\n");
        file_put_contents("$this->dir/site.php", "<?php\n");
        file_put_contents("$this->dir/site.http", "# docroot public\n# router public/index.php\nGET /books/kindred\n");

        self::assertStringEndsWith("\nYou asked for /books/kindred\n", Web::output("$this->dir/site.php"));
        self::assertSame('php -S localhost:8000 -t public public/index.php', Web::command("$this->dir/site.php"));
    }

    public function testAPathOutsideTheFolderIsRefused(): void
    {
        $this->expectException(\RuntimeException::class);
        Web::parse("# docroot ../..\nGET /\n");
    }

    public function testAMultipartBodySendsAFile(): void
    {
        file_put_contents("$this->dir/upload.php", "<?php\n\$f = \$_FILES['cover'];\necho \$f['name'], ' ', \$f['size'], ' ', \$f['error'], ' ', file_get_contents(\$f['tmp_name']) === file_get_contents(__DIR__ . '/cover.bin') ? 'same' : 'differs', ' ', \$_POST['title'], \"\\n\";\n");
        file_put_contents("$this->dir/cover.bin", "\x89PNG\r\n\x1a\n\0\xff");
        $http = "POST /upload.php\nContent-Type: multipart/form-data; boundary=XYZ\n\n--XYZ\nContent-Disposition: form-data; name=\"title\"\n\nKindred\n"
            . "--XYZ\nContent-Disposition: form-data; name=\"cover\"; filename=\"c.png\"\nContent-Type: image/png\n\n< cover.bin\n--XYZ--\n";

        [$response] = responses_of($this->dir, $http);
        self::assertSame("c.png 10 0 same Kindred\n", $response['body']);
    }

    public function testAMultipartFileOutsideTheFolderIsRefused(): void
    {
        $this->expectException(\RuntimeException::class);
        responses_of($this->dir, "POST /x.php\nContent-Type: multipart/form-data; boundary=X\n\n--X\n< ../../etc/passwd\n--X--\n");
    }

    public function testAFormThatSavesChangesOnlyTheCopy(): void
    {
        file_put_contents("$this->dir/count.php", "<?php\nfile_put_contents(__DIR__ . '/count.txt', (string) ((int) @file_get_contents(__DIR__ . '/count.txt') + 1));\necho file_get_contents(__DIR__ . '/count.txt'), \"\\n\";\n");
        $responses = responses_of($this->dir, "POST /count.php\n\n###\nPOST /count.php\n");

        self::assertSame("2\n", $responses[1]['body']);
        self::assertSame(200, $responses[1]['status']);
        self::assertSame('text/html; charset=UTF-8', $responses[1]['headers']['content-type']);
        self::assertFileDoesNotExist("$this->dir/count.txt");
    }
}
