<?php

declare(strict_types=1);

// Exercise 33, beginner: one interface, two languages.
//
// The interface Greeting is given. Write two classes that implement it:
// - EnglishGreeting: text('Amira') returns "Hello, Amira.";
// - GermanGreeting: text('Jonas') returns "Hallo, Jonas.".
// Then write a function greetAll(Greeting $greeting, array $names): array that
// returns the greeting for each name, in order. It must work with any Greeting.

interface Greeting
{
    public function text(string $name): string;
}

// Check your work: composer exercise 33 beginner
