<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class NoteTest extends TestCase
{
    private const POST = "POST /note.php\nContent-Type: application/x-www-form-urlencoded\n\n";

    public function testTheFormCarriesARandomToken(): void
    {
        [$form] = responses_of(dirname(subject(__DIR__, 'note.php')), "GET /note.php\n");

        self::assertMatchesRegularExpression('/<input type="hidden" name="_token" value="[0-9a-f]{64}">/', $form['body']);
    }

    public function testOnlyThePostWithTheTokenIsSaved(): void
    {
        [, $without, $wrong, $right] = responses_of(dirname(subject(__DIR__, 'note.php')), implode("\n\n###\n", [
            "# capture token /name=\"_token\" value=\"([^\"]+)\"/\nGET /note.php",
            self::POST . 'note=hi',
            self::POST . 'note=hi&_token=guessed',
            self::POST . 'note=hi&_token={{token}}',
        ]) . "\n");

        self::assertSame([403, "Expired.\n"], [$without['status'], $without['body']]);
        self::assertSame(403, $wrong['status']);
        self::assertSame([200, "Saved.\n"], [$right['status'], $right['body']]);
    }
}
