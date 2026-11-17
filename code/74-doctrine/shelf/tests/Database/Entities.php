<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use Doctrine\DBAL\Driver\AbstractSQLiteDriver\Middleware\EnableForeignKeys;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Shelf\Doctrine\IsbnType;

/**
 * Doctrine for the repository tests (chapter 74): a new SQLite database in memory for every test,
 * with Shelf's tables made from the entities' attributes, set up as config/packages/doctrine.yaml does.
 */
final class Entities
{
    public static function manager(): EntityManager
    {
        if (!Type::hasType(IsbnType::NAME)) {
            Type::addType(IsbnType::NAME, IsbnType::class);
        }
        $config = ORMSetup::createAttributeMetadataConfig([dirname(__DIR__, 2) . '/src'], isDevMode: true);
        $config->enableNativeLazyObjects(true);
        $config->setNamingStrategy(new UnderscoreNamingStrategy());
        $config->setMiddlewares([new EnableForeignKeys()]);
        $entities = new EntityManager(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config), $config);
        new SchemaTool($entities)->createSchema($entities->getMetadataFactory()->getAllMetadata());

        return $entities;
    }
}
