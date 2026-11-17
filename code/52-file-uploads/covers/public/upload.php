<?php

declare(strict_types=1);

// A safe upload: checked by its content, renamed, and stored outside public/.

const MAX_BYTES = 2 * 1024 * 1024;
const TYPES = ['image/png' => 'png', 'image/jpeg' => 'jpg']; // what the file IS => its extension
const STORAGE = __DIR__ . '/../storage';

/** The reason the upload can't be used, or null. */
function problem(mixed $file): ?string
{
    if (!is_array($file) || !is_int($file['error'] ?? null) || !is_string($file['tmp_name'] ?? null)) {
        return 'Choose one file.';
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return 'The file is larger than 2 MB.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return 'The upload failed. Please try again.';
    }
    if (filesize($file['tmp_name']) > MAX_BYTES) {
        return 'The file is larger than 2 MB.';
    }
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(TYPES[$type]) || getimagesize($file['tmp_name']) === false) {
        return 'Only PNG and JPEG images, please.';
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['cover'] ?? null;
    $problem = problem($file);
    if ($problem === null && is_array($file) && is_string($file['tmp_name'])) {
        $type = (string) (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $name = bin2hex(random_bytes(16)) . '.' . TYPES[$type];
        if (!is_dir(STORAGE)) {
            mkdir(STORAGE, 0o750);
        }
        move_uploaded_file($file['tmp_name'], STORAGE . "/$name");
        header("Location: /cover.php?file=$name", true, 303);
        exit;
    }
    http_response_code(422);
    echo '<p class="error">', htmlspecialchars((string) $problem), "</p>\n";
}
?>
<form method="post" action="/upload.php" enctype="multipart/form-data">
    <input type="file" name="cover" accept="image/png, image/jpeg">
    <button>Upload</button>
</form>
