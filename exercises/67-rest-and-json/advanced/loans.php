<?php

declare(strict_types=1);

// A new loan, sent by the library's desk app: POST /loans.php with a JSON body such as
// {"title": "Kindred", "member": "Amira"}.
// - Body that isn't JSON: 400, {"error": "The body must be JSON."}
// - Not an object with a "title" and a "member" that are known (in BOOKS and MEMBERS): 422,
//   {"error": "A loan needs a known title and a known member."}
// - Otherwise: 201, the header Location: /loan.php?id=7 and the loan {"id": 7, "title": ..., "member": ...}.
//   (This exercise saves nothing; every new loan gets the next ID, 7.)
// - Any other method: 405, Allow: POST, {"error": "Use POST."}
// Every answer is JSON, with Content-Type: application/json.

const BOOKS = ['Kindred', 'Kitchen', 'Things Fall Apart'];
const MEMBERS = ['Amira', 'Jonas'];
const NEXT_ID = 7;

$sent = json_decode((string) file_get_contents('php://input'), true);
echo json_encode(['id' => NEXT_ID, 'title' => $sent['title'], 'member' => $sent['member']]);
