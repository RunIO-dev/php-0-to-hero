<?php

declare(strict_types=1);

// Exercise 19, intermediate: a member who isn't there.
//
// Write a function findMember($members, $email) that returns the member map
// with that email address. When there is none, it throws a MemberNotFound
// exception with the message
//   No member with the email sula@example.org
// Emails are compared in any case: AMIRA@example.org finds amira@example.org.
//
// Check your work: composer exercise 19 intermediate

final class MemberNotFound extends RuntimeException {}
