<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tools\Batches;

final class BatchesTest extends TestCase
{
    public function testFindsDeclaredFunctionsAndClasses(): void
    {
        $code = "<?php\nfinal class BookNotFound extends RuntimeException {}\n"
            . "function lateFee(int \$d): int { return \$d; }\n"
            . "\$f = function (): int { return 1; };\n\$g = fn(): int => 2;\necho BookNotFound::class;\n";

        self::assertSame(['booknotfound', 'latefee'], Batches::declared($code));
    }

    public function testFindsGlobalConstantsButNotClassConstants(): void
    {
        $code = "<?php\nconst BOOKS = [];\nfinal class Shelf { public const int SIZE = 3; }\nconst FLAGS = 1;\n";

        self::assertSame(['const books', 'shelf', 'const flags'], Batches::declared($code));
    }

    public function testSameNamesGoIntoDifferentBatches(): void
    {
        $dir = sys_get_temp_dir() . '/batches-' . bin2hex(random_bytes(4));
        mkdir($dir);
        try {
            file_put_contents("$dir/a.php", "<?php\nfunction lateFee() {}\n");
            file_put_contents("$dir/b.php", "<?php\nfunction lateFee() {}\n");
            file_put_contents("$dir/c.php", "<?php\nfunction other() {}\n");
            file_put_contents("$dir/d.php", "<?php\necho 1;\n");

            self::assertSame(
                [["$dir/a.php", "$dir/c.php", "$dir/d.php"], ["$dir/b.php"]],
                Batches::split(["$dir/a.php", "$dir/b.php", "$dir/c.php", "$dir/d.php"]),
            );
        } finally {
            \Tools\Run::remove($dir);
        }
    }

    /** A solution's files are loaded by its test, not by each other: one exercise level is one unit, with its subfolders. */
    public function testTheFilesOfOneExerciseStayTogether(): void
    {
        $dir = sys_get_temp_dir() . '/batches-' . bin2hex(random_bytes(4));
        $level = "$dir/solutions/76-security/intermediate";
        mkdir("$level/entities", 0o777, true);
        try {
            file_put_contents("$dir/a.php", "<?php\nfinal class Review {}\n");
            file_put_contents("$level/Review.php", "<?php\nfinal class Review {}\n");
            file_put_contents("$level/ReviewVoter.php", "<?php\nfinal class ReviewVoter { public function f(Review \$r): void {} }\n");
            file_put_contents("$level/entities/Book.php", "<?php\nfinal class Book {}\n");

            self::assertSame(
                [["$dir/a.php"], ["$level/Review.php", "$level/ReviewVoter.php", "$level/entities/Book.php"]],
                Batches::split(["$dir/a.php", "$level/Review.php", "$level/ReviewVoter.php", "$level/entities/Book.php"]),
            );
        } finally {
            \Tools\Run::remove($dir);
        }
    }

    public function testFilesThatRequireEachOtherStayTogether(): void
    {
        $dir = sys_get_temp_dir() . '/batches-' . bin2hex(random_bytes(4));
        mkdir("$dir/shelf", 0o777, true);
        try {
            file_put_contents("$dir/a.php", "<?php\nfunction loadBooks() {}\n");
            file_put_contents("$dir/shelf/books.php", "<?php\nfunction loadBooks() {}\n");
            file_put_contents("$dir/shelf/shelf.php", "<?php\nrequire_once __DIR__ . '/books.php';\nloadBooks();\n");

            self::assertSame(
                [["$dir/a.php"], ["$dir/shelf/books.php", "$dir/shelf/shelf.php"]],
                Batches::split(["$dir/a.php", "$dir/shelf/books.php", "$dir/shelf/shelf.php"]),
            );
        } finally {
            \Tools\Run::remove($dir);
        }
    }
}
