<?php

declare(strict_types=1);

// Exercise 44, advanced: one answer, two formats.
//
// When the request's Accept header contains application/json, answer with the books as
// JSON (a list of objects with title and year) and Content-Type: application/json.
// Otherwise, answer in plain text, one line per book: "Kindred (1979)", and
// Content-Type: text/plain; charset=UTF-8. The header is in $_SERVER['HTTP_ACCEPT'], if sent.

const BOOKS = [['title' => 'Kindred', 'year' => 1979], ['title' => 'Sula', 'year' => 1973]];

// your code here

// Check your work: composer exercise 44 advanced
