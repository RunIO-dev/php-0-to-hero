<?php

declare(strict_types=1);

// Exercise 77, beginner: tests for a voter.
//
// loans.php is right. Write tests for LoanVoter: who may return and extend which loan, a visitor,
// and the questions it must leave to other voters. Each file in bugs/ is loans.php with one mistake,
// and your tests must fail on every one. Ask the voter as Symfony does: $voter->vote($token, $loan, [...]),
// with a UsernamePasswordToken for a member, and a NullToken for a visitor.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/loans.php';

final class LoanVoterTest extends TestCase
{
    public function testTheBorrowerMayReturnTheirLoan(): void
    {
        self::markTestIncomplete('Write this test, then the others.');
    }

    // your other tests here
}

// Check your work: composer exercise 77 beginner
