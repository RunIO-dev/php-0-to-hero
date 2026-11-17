<?php

declare(strict_types=1);

use Symfony\Component\Validator\Constraints as Assert;

/** A reader's review of a book, as a form or a program sends it: every field may be missing. */
final class Review
{
    #[Assert\NotBlank(message: 'Which book?')]
    public ?string $book = null;

    #[Assert\NotNull(message: 'Give the book from 1 to 5 stars.')]
    #[Assert\Range(notInRangeMessage: 'Give the book from {{ min }} to {{ max }} stars.', min: 1, max: 5)]
    public ?int $stars = null;

    #[Assert\Length(max: 500, maxMessage: 'A review has at most {{ limit }} characters.')]
    public ?string $text = null;

    #[Assert\Email(message: '{{ value }} is not an email address.')]
    public ?string $email = null;
}
