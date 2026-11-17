<?php

declare(strict_types=1);

namespace Shelf\Web;

use finfo;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function Shelf\tidy;

/**
 * Book covers: PNG or JPEG images of up to 2 MB, checked by their content, and stored in a folder
 * outside public/, under a name made from the book's title. Nothing the visitor sent, not the
 * file's name nor its claimed type, decides where or how a file is stored. From chapter 73, an upload
 * is Symfony's UploadedFile.
 */
final readonly class Covers
{
    public const int MAX_BYTES = 2 * 1024 * 1024;

    /** What a file is, by its content => the extension it is stored with. */
    private const array TYPES = ['image/png' => 'png', 'image/jpeg' => 'jpg'];

    public function __construct(private string $dir) {}

    /** Throws an InvalidArgumentException with a reason for the visitor if $upload can't be a cover. */
    public function check(UploadedFile $upload): void
    {
        if ($upload->getError() === UPLOAD_ERR_INI_SIZE || $upload->getError() === UPLOAD_ERR_FORM_SIZE) {
            throw new InvalidArgumentException('The cover is larger than 2 MB.');
        }
        if (!$upload->isValid() || !is_file($upload->getPathname())) {
            throw new InvalidArgumentException('The cover could not be uploaded. Please try again.');
        }
        if (filesize($upload->getPathname()) > self::MAX_BYTES) {
            throw new InvalidArgumentException('The cover is larger than 2 MB.');
        }
        if ($this->extension($upload->getPathname()) === null || getimagesize($upload->getPathname()) === false) {
            throw new InvalidArgumentException('A cover must be a PNG or JPEG image.');
        }
    }

    /** Stores a checked upload as the cover of $title, in place of an earlier one. */
    public function save(string $title, UploadedFile $upload): void
    {
        $this->check($upload);
        if (!is_dir($this->dir) && !mkdir($this->dir, 0o750, true)) {
            throw new RuntimeException("Cannot create $this->dir");
        }
        foreach (self::TYPES as $extension) {
            if (is_file($this->base($title) . ".$extension")) {
                unlink($this->base($title) . ".$extension");
            }
        }
        if (!copy($upload->getPathname(), $this->base($title) . '.' . $this->extension($upload->getPathname()))) {
            throw new RuntimeException("Cannot save the cover of $title");
        }
    }

    /**
     * The stored cover of $title, if there is one.
     *
     * @return array{type: string, path: string}|null
     */
    public function find(string $title): ?array
    {
        foreach (self::TYPES as $type => $extension) {
            if (is_file($this->base($title) . ".$extension")) {
                return ['type' => $type, 'path' => $this->base($title) . ".$extension"];
            }
        }

        return null;
    }

    /** Deletes the cover of $title, if there is one (chapter 55: the book was removed). */
    public function remove(string $title): void
    {
        $cover = $this->find($title);
        if ($cover !== null && !unlink($cover['path'])) {
            throw new RuntimeException("Cannot delete the cover of $title");
        }
    }

    /** The file name without extension: a hash of the title, so any title gives a safe name. */
    private function base(string $title): string
    {
        return $this->dir . '/' . hash('sha256', mb_strtolower(tidy($title)));
    }

    private function extension(string $path): ?string
    {
        $type = (new finfo(FILEINFO_MIME_TYPE))->file($path);

        return is_string($type) ? self::TYPES[$type] ?? null : null;
    }
}
