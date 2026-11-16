<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tools\Patterns;

final class PatternsTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function forbidden(): iterable
    {
        yield 'silenced error' => ['$x = @file_get_contents("a.txt");'];
        yield 'eval' => ['eval($code);'];
        yield 'global' => ['function f() { global $db; }'];
        yield 'mysql_' => ['mysql_query("SELECT 1");'];
        yield 'extract' => ['extract($_POST);'];
        yield 'md5 password' => ['$hash = md5($password);'];
        yield 'sha1 password' => ['$h = sha1($_POST["pass"]);'];
        yield 'echo GET' => ['echo $_GET["name"];'];
        yield 'echo GET in text' => ['echo "Hello " . $_GET["name"];'];
        yield 'SQL joined' => ['$sql = "SELECT * FROM books WHERE id = " . $id;'];
        yield 'SQL interpolated' => ['$sql = "SELECT * FROM books WHERE id = $id";'];
        yield 'SQL interpolated braces' => ['$sql = "DELETE FROM loans WHERE id = {$id}";'];
        yield 'UPDATE joined' => ['$pdo->query("UPDATE books SET title = \'" . $t . "\'");'];
        yield 'fully qualified md5' => ['$h = \\md5($password);'];
        yield 'fully qualified extract' => ['\\extract($_POST);'];
        yield 'hash of a password' => ['$h = hash("sha256", $userPassword);'];
        yield 'sha1 of pwd' => ['$h = sha1($pwd);'];
        yield 'printf GET' => ['printf("Hi %s", $_GET["name"]);'];
        yield 'echo PHP_SELF' => ['echo $_SERVER["PHP_SELF"];'];
        yield 'sprintf SQL' => ['$sql = sprintf("SELECT * FROM books WHERE id = %s", $id);'];
    }

    /** @return iterable<string, array{string}> */
    public static function allowed(): iterable
    {
        yield 'escaped echo' => ['echo htmlspecialchars($_GET["name"], ENT_QUOTES);'];
        yield 'prepared' => ['$s = $pdo->prepare("SELECT * FROM books WHERE id = ?"); $s->execute([$id]);'];
        yield 'md5 of a file' => ['$sum = md5_file("cover.jpg"); $etag = md5($body);'];
        yield 'password_hash' => ['$hash = password_hash($password, PASSWORD_DEFAULT);'];
        yield 'word in comment' => ['// never use eval() or global $x or @'];
        yield 'word in string' => ['echo "Do not use extract() or mysql_query().";'];
        yield 'plain SQL text' => ['$sql = "SELECT title FROM books";'];
        yield 'email address' => ['$to = "ana@example.org";'];
        yield 'method named extract' => ['$zip->extract($dir);'];
        yield 'SQL with int cast' => ['$sql = "SELECT * FROM u WHERE id = " . (int) $id;'];
        yield 'SQL with constant' => ['$sql = "SELECT * FROM " . self::TABLE;'];
        yield 'prose with from' => ['echo "Select a file from " . $dir;'];
        yield 'echo int cast' => ['echo (int) $_GET["id"];'];
        yield 'md5 compass' => ['$h = md5($compass);'];
    }

    #[DataProvider('forbidden')]
    public function testFindsForbiddenPattern(string $code): void
    {
        self::assertCount(1, Patterns::check('x.php', "<?php\n" . $code));
    }

    #[DataProvider('allowed')]
    public function testAllowsSafeCode(string $code): void
    {
        self::assertSame([], Patterns::check('x.php', "<?php\n" . $code));
    }

    public function testSkipsMarkedBadExampleOnlyInCode(): void
    {
        self::assertSame([], Patterns::check('code/46-web/login.bad.php', '<?php echo $_GET["x"];'));
        self::assertCount(1, Patterns::check('solutions/46-web/login.bad.php', "<?php\ndeclare(strict_types=1);\necho \$_GET['x'];"));
    }

    public function testShortEchoTagAndCloseTagEndTheStatement(): void
    {
        $html = "<p><?= \$_GET['x'] ?></p>\n<p><?php echo \$_GET['y'] ?></p>\n<?php echo htmlspecialchars('ok');";
        self::assertCount(2, Patterns::check('page.php', $html));
    }

    public function testReportsFileAndLine(): void
    {
        self::assertSame(
            ['x.php:3: eval() runs text as code; never needed in this course'],
            Patterns::check('x.php', "<?php\n\neval(\$a);"),
        );
    }

    public function testStrictTypesFromChapter16(): void
    {
        $plain = "<?php\n\necho 1;\n";
        $strict = "<?php\n\ndeclare(strict_types=1);\n\necho 1;\n";
        self::assertSame([], Patterns::check('code/15-functions/a.php', $plain));
        self::assertCount(1, Patterns::check('code/16-types/a.php', $plain));
        self::assertCount(1, Patterns::check('exercises/20-closures/beginner/a.php', $plain));
        self::assertCount(1, Patterns::check('shelf/shelf.php', $plain));
        self::assertSame([], Patterns::check('solutions/16-types/beginner/a.php', $strict));
        self::assertSame([], Patterns::check('code/14-arrays/a.php', $plain));
    }
}
