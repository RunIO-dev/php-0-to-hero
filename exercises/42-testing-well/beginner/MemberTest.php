<?php

declare(strict_types=1);

// Exercise 42, beginner: testing exceptions.
//
// member.php is right. Write tests for newMember(): one for a welcome, and one for each
// way it refuses, with expectException and expectExceptionMessage. Each file in bugs/
// is member.php with one mistake; your tests must fail on every one of them.
// Test the edge: how old is the youngest member who may join?

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/member.php';

final class MemberTest extends TestCase
{
    public function testAWelcome(): void
    {
        self::assertSame('Welcome, Amira!', newMember('Amira', 30));
    }

    // your tests here
}

// Check your work: composer exercise 42 beginner
