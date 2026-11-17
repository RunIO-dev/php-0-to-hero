<?php

declare(strict_types=1);

// Exercise 52, beginner: what went wrong with an upload.
//
// PHP describes each uploaded file in $_FILES with an 'error' code. uploadProblem($error) turns
// the code into a message for the visitor, or null when the upload worked:
//   UPLOAD_ERR_OK                              → null
//   UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE  → 'The file is too large.'
//   UPLOAD_ERR_NO_FILE                         → 'Please choose a file.'
//   UPLOAD_ERR_PARTIAL                         → 'The upload was cut off. Please try again.'
//   every other code (a problem of the server) → 'The upload failed.'
// Use match.

function uploadProblem(int $error): ?string
{
    return null;
}
// Check your work: composer exercise 52 beginner
