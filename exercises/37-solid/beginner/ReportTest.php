<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReportTest extends TestCase
{
    public function testTheLines(): void
    {
        require_once subject(__DIR__, 'report.php');

        $report = new LoanReport(['Kindred' => 'Amira', 'Sula' => 'Kenji']);

        self::assertSame(['Kindred: Amira', 'Sula: Kenji'], $report->lines());
        self::assertSame([], (new LoanReport([]))->lines());
    }

    public function testSavingIsSeparate(): void
    {
        require_once subject(__DIR__, 'report.php');

        $path = tempnam(sys_get_temp_dir(), 'report');
        try {
            saveLines((new LoanReport(['Kindred' => 'Amira']))->lines(), (string) $path);
            self::assertSame("Kindred: Amira\n", file_get_contents((string) $path));
        } finally {
            unlink((string) $path);
        }

        $class = new ReflectionClass(LoanReport::class);
        $code = implode('', array_slice((array) file((string) $class->getFileName()), $class->getStartLine() - 1, $class->getEndLine() - $class->getStartLine() + 1));
        self::assertStringNotContainsString('file_put_contents', $code, 'LoanReport should not write files.');
    }
}
