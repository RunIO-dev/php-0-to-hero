<?php

declare(strict_types=1);

namespace Shelf\Form;

use Shelf\Book;
use Shelf\Validator\ValidIsbn;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * What the form to add a book sends, and the API's JSON (chapter 75), before it is a Book: every field
 * may be missing or wrong, so every one may be null. The attributes are the rules; the validator
 * checks them all, and names every mistake at once.
 */
final class BookData
{
    #[Assert\NotBlank(message: 'A book needs a title.')]
    #[Assert\Length(max: 200, maxMessage: 'A title has at most {{ limit }} characters.')]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'A book needs an author.')]
    #[Assert\Length(max: 200, maxMessage: 'A name has at most {{ limit }} characters.')]
    public ?string $author = null;

    #[Assert\NotNull(message: 'A book needs a year.')]
    #[Assert\Range(notInRangeMessage: 'The year is from {{ min }} to {{ max }}.', min: Book::FIRST_YEAR, max: Book::LAST_YEAR)]
    public ?int $year = null;

    #[ValidIsbn]
    public ?string $isbn = null;

    /** Checked here by its type and size; Covers checks the content again before it stores it (chapter 52). */
    #[Assert\Image(
        maxSize: '2M',
        mimeTypes: ['image/png', 'image/jpeg'],
        maxSizeMessage: 'The cover is larger than 2 MB.',
        mimeTypesMessage: 'A cover must be a PNG or JPEG image.',
    )]
    public ?UploadedFile $cover = null;

    /** @return list<string> what Library::add() takes: title, author, year and, if there is one, the ISBN */
    public function fields(): array
    {
        return [(string) $this->title, (string) $this->author, (string) $this->year, ...($this->isbn ?? '') === '' ? [] : [(string) $this->isbn]];
    }
}
