<?php

declare(strict_types=1);

// ✗ "SOLID" applied to a one-line rule: three names, two files' worth of code, nothing gained.
interface TitleNormalizerInterface
{
    public function normalize(string $title): string;
}

final class LowerCaseTitleNormalizer implements TitleNormalizerInterface
{
    public function normalize(string $title): string
    {
        return mb_strtolower(trim($title));
    }
}

final readonly class TitleComparator
{
    public function __construct(private TitleNormalizerInterface $normalizer) {}

    public function same(string $a, string $b): bool
    {
        return $this->normalizer->normalize($a) === $this->normalizer->normalize($b);
    }
}
