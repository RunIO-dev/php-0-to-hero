<?php

declare(strict_types=1);

namespace Shelf\Tests\Form;

use PHPUnit\Framework\TestCase;
use Shelf\Form\BookData;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** The rules of a new book (chapter 75), checked by Symfony's validator as the form and the API do. */
final class BookDataTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
    }

    public function testAGoodBookHasNoMistakes(): void
    {
        self::assertSame([], $this->mistakes(self::book('Kindred', 'Octavia E. Butler', 1979, '978-0-8070-8369-7')));
        self::assertSame([], $this->mistakes(self::book('Sula', 'Toni Morrison', 1973)), 'The ISBN is optional');
    }

    public function testEveryMistakeIsNamed(): void
    {
        self::assertSame([
            'title' => 'A book needs a title.',
            'author' => 'A book needs an author.',
            'year' => 'A book needs a year.',
        ], $this->mistakes(new BookData()));
    }

    public function testTheLimits(): void
    {
        self::assertSame([
            'title' => 'A title has at most 200 characters.',
            'year' => 'The year is from 1 to 2100.',
            'isbn' => '"12345" is not a valid ISBN-13.',
        ], $this->mistakes(self::book(str_repeat('a', 201), 'Someone', 10000, '12345')));
    }

    public function testACoverMustBeAnImage(): void
    {
        $book = self::book('Sula', 'Toni Morrison', 1973);
        $book->cover = new UploadedFile(__DIR__ . '/../Web/not-a-cover.png', 'sula.png', 'image/png', test: true);

        self::assertSame(['cover' => 'A cover must be a PNG or JPEG image.'], $this->mistakes($book));
    }

    public function testFieldsAreWhatLibraryTakes(): void
    {
        self::assertSame(['Kindred', 'Octavia E. Butler', '1979'], self::book('Kindred', 'Octavia E. Butler', 1979)->fields());
        self::assertSame(['Kindred', 'Octavia E. Butler', '1979', '9780807083697'], self::book('Kindred', 'Octavia E. Butler', 1979, '9780807083697')->fields());
    }

    private static function book(string $title, string $author, int $year, ?string $isbn = null): BookData
    {
        $book = new BookData();
        $book->title = $title;
        $book->author = $author;
        $book->year = $year;
        $book->isbn = $isbn;

        return $book;
    }

    /** @return array<string, string> field => its first mistake */
    private function mistakes(BookData $book): array
    {
        $mistakes = [];
        foreach ($this->validator->validate($book) as $violation) {
            $mistakes[$violation->getPropertyPath()] ??= (string) $violation->getMessage();
        }

        return $mistakes;
    }
}
