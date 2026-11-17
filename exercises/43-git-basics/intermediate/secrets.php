<?php

declare(strict_types=1);

// Exercise 43, intermediate: find secrets before they are committed.
//
// findSecrets() gets the files about to be committed, path => content, and returns
// one message per finding, file by file, in this order:
//   "config/.env: an environment file"   a file named .env, or .env.something,
//                                         but not .env.example or .env.dist
//   "deploy/server.pem: a key file"       a file ending in .pem or .key
//   "notes.txt: a private key"            content with "-----BEGIN" and "PRIVATE KEY-----"
//   "src/Mail.php line 7: a password or key"
//       a line that sets a name containing password, secret, token or api_key (or apikey),
//       in any case, with = or :, to a value of at least 8 characters, quotes not counted:
//       $password = 'correct-horse';   API_KEY=4f9a1c7e2b8d   "token": "abcdef123456"
//       Lines numbers start at 1. getenv('API_KEY') or a value under 8 characters are fine.

/**
 * @param array<string, string> $files path => content
 * @return list<string>
 */
function findSecrets(array $files): array
{
    return []; // your code here
}

// Check your work: composer exercise 43 intermediate
