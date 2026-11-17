<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MessagesTest extends TestCase
{
    /** @param list<string> $problems */
    #[DataProvider('messages')]
    public function testTheProblems(string $message, array $problems): void
    {
        require_once subject(__DIR__, 'messages.php');

        self::assertSame($problems, messageProblems($message));
    }

    /** @return array<string, array{string, list<string>}> */
    public static function messages(): array
    {
        $why = "Move the due date into Loan\n\nLoan::start can be tested without a repository.\n";

        return [
            'a good summary' => ['Move the due date into Loan', []],
            'a good summary and body' => [$why, []],
            'a short word ending in ed' => ['Need a test for renew', []],
            'empty' => ["  \n", ['empty message']],
            'too long' => ['Move ' . str_repeat('a', 70), ['summary longer than 72 characters']],
            'lower case' => ['move the due date', ['start the summary with a capital letter']],
            'full stop' => ['Move the due date.', ['no full stop at the end of the summary']],
            'past tense' => ['Added tests for Loan', ['write the summary as an instruction: Add, not Added']],
            'continuous' => ['Fixing the ISBN check', ['write the summary as an instruction: Add, not Added']],
            'no blank line' => ["Move the due date\nIt is easier to test.", ['leave a blank line after the summary']],
            'all at once' => ["fixed stuff.\nmore", [
                'start the summary with a capital letter',
                'no full stop at the end of the summary',
                'write the summary as an instruction: Add, not Added',
                'leave a blank line after the summary',
            ]],
        ];
    }
}
