<?php

declare(strict_types=1);

// ✗ Every field of the request, straight into the object.

use Symfony\Component\HttpFoundation\Request;

/** @param object $member */
function updateMember(Request $request, object $member): void
{
    foreach ($request->request->all() as $field => $value) {
        $member->$field = $value; // name, email... and role, if someone sends role=staff
    }
}
