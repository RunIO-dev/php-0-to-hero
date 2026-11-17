<?php

declare(strict_types=1);

interface Greeting
{
    public function text(string $name): string;
}

final class EnglishGreeting implements Greeting
{
    public function text(string $name): string
    {
        return "Hello, $name.";
    }
}

final class GermanGreeting implements Greeting
{
    public function text(string $name): string
    {
        return "Hallo, $name.";
    }
}

/**
 * @param list<string> $names
 * @return list<string>
 */
function greetAll(Greeting $greeting, array $names): array
{
    return array_map(fn(string $name): string => $greeting->text($name), $names);
}
