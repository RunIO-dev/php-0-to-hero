<?php

declare(strict_types=1);

namespace Shelf;

use MessageFormatter;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * What a refusal says, in any language (chapter 87). The text is English, with {placeholders} in ICU's
 * MessageFormat; it is also the key the translations look it up by, in translations/messages+intl-icu.*.
 * getMessage() stays English, for the logs, the tests and the console; a page passes the exception
 * itself to |trans, and gets it in the visitor's language.
 */
trait Says
{
    private string $text = '';

    /** @var array<string, string|int|\DateTimeInterface> */
    private array $values = [];

    /**
     * @param array<string, string|int|\DateTimeInterface> $values a number is formatted as one ("1,234"):
     *   a year or an ID goes in as a string
     */
    public function __construct(string $text = '', array $values = [])
    {
        $this->text = $text;
        $this->values = $values;
        parent::__construct($values === [] ? $text : (string) MessageFormatter::formatMessage('en', $text, $values));
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->text, $this->values, 'messages', $locale);
    }
}
