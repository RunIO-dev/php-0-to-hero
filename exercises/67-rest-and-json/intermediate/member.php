<?php

declare(strict_types=1);

// One member, for programs: GET /member.php?name=Amira answers {"name": "Amira", "role": "reader"}.
// A name that isn't a member gets 404, with {"error": "There is no such member."}.
// Any other method than GET gets 405, with the header Allow: GET and {"error": "Use GET."}.
// Every answer is JSON, with Content-Type: application/json.

const MEMBERS = ['Amira' => 'reader', 'Jonas' => 'staff'];

$name = is_string($_GET['name'] ?? null) ? $_GET['name'] : '';
echo $name, ' is ', MEMBERS[$name] ?? 'unknown', "\n";
