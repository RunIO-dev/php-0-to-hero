<?php

declare(strict_types=1);

// Exercise 52, beginner: what went wrong with an upload.

function uploadProblem(int $error): ?string
{
    return match ($error) {
        UPLOAD_ERR_OK => null,
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The file is too large.',
        UPLOAD_ERR_NO_FILE => 'Please choose a file.',
        UPLOAD_ERR_PARTIAL => 'The upload was cut off. Please try again.',
        default => 'The upload failed.',
    };
}
