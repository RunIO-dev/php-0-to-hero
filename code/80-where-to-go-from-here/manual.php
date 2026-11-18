<?php

declare(strict_types=1);

// Reading the manual (chapter 80): every function's page starts with its signature. PHP knows the same
// signature itself, through Reflection, so this script writes it the way the manual does.

function signature(string $function): string
{
    $reflection = new ReflectionFunction($function);
    $parameters = [];
    foreach ($reflection->getParameters() as $parameter) {
        $text = $parameter->getType() . ' $' . $parameter->getName();
        if ($parameter->isDefaultValueAvailable()) {
            $text .= ' = ' . strtolower(var_export($parameter->getDefaultValue(), true));
        }
        $parameters[] = $text;
    }

    return $function . '(' . implode(', ', $parameters) . '): ' . $reflection->getReturnType();
}

foreach (['str_contains', 'array_search', 'json_decode'] as $function) {
    echo signature($function), "\n";
}
