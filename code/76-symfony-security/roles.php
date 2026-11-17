<?php

declare(strict_types=1);

// A role hierarchy: a role that includes others, as security.yaml's role_hierarchy writes it.

use Symfony\Component\Security\Core\Role\RoleHierarchy;

require __DIR__ . '/vendor/autoload.php';

$roles = new RoleHierarchy([
    'ROLE_STAFF' => ['ROLE_READER'],
    'ROLE_ADMIN' => ['ROLE_STAFF'],
]);

foreach (['ROLE_READER', 'ROLE_STAFF', 'ROLE_ADMIN'] as $role) {
    echo $role, ': ', implode(', ', $roles->getReachableRoleNames([$role])), "\n";
}
