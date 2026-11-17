<?php

declare(strict_types=1);

namespace Shelf\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use Shelf\Isbn;

/**
 * A column type of our own (chapter 74): an Isbn in PHP, its 13 digits in the database. Doctrine
 * calls it for every value on the way in and out, so the rest of Shelf only ever sees Isbn objects.
 * config/packages/doctrine.yaml registers it.
 */
final class IsbnType extends Type
{
    public const string NAME = 'isbn';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 13] + $column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Isbn
    {
        return is_string($value) ? new Isbn($value) : null;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return match (true) {
            $value === null => null,
            $value instanceof Isbn => $value->digits,
            default => throw InvalidType::new($value, self::NAME, ['null', Isbn::class]),
        };
    }
}
