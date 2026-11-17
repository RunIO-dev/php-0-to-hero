<?php

declare(strict_types=1);

// The examples' entity manager: SQLite in memory, with the tables made from the entities in src/.
// With $showSql, it prints each SQL statement as Doctrine sends it, with its parameters.

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Log\AbstractLogger;

require __DIR__ . '/vendor/autoload.php';

final class SqlPrinter extends AbstractLogger
{
    public bool $on = false;

    public function log($level, string|Stringable $message, array $context = []): void
    {
        if ($this->on && isset($context['sql']) && is_string($context['sql'])) {
            $params = isset($context['params']) && is_array($context['params']) ? ' ' . json_encode(array_values($context['params'])) : '';
            echo "  SQL: {$context['sql']}$params\n";
        }
    }
}

function entities(bool $showSql = false): EntityManager
{
    $printer = new SqlPrinter();
    $config = ORMSetup::createAttributeMetadataConfig([__DIR__ . '/src'], isDevMode: true);
    $config->enableNativeLazyObjects(true);
    $config->setMiddlewares([new Middleware($printer)]);
    $entities = new EntityManager(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config), $config);
    new SchemaTool($entities)->createSchema($entities->getMetadataFactory()->getAllMetadata());
    $printer->on = $showSql;

    return $entities;
}
