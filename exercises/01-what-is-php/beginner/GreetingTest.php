<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GreetingTest extends TestCase
{
    public function testYourPredictionMatchesWhatPhpPrints(): void
    {
        $prediction = (string) file_get_contents(subject(__DIR__, 'prediction.txt'));
        $actual = output_of(__DIR__ . '/greeting.php');

        self::assertSame(rtrim($actual), rtrim($prediction), 'Compare your prediction.txt with what PHP printed (shown as "actual").');
    }
}
