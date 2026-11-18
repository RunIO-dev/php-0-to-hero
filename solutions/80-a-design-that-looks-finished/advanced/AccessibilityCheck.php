<?php

declare(strict_types=1);

// A page's accessibility, checked in a test (chapter 80), as Shelf's AccessibilityTest does for each page.
// problems() reads a whole HTML page with PHP's HTML5 parser, Dom\HTMLDocument, and names each problem,
// in this order, each once per element:
// - "no lang": <html> has no lang attribute, or an empty one;
// - "h1: 0" or "h1: 2": not exactly one <h1>;
// - "heading skips: h2 to h4": a heading more than one level below the heading before it;
// - "image without alt: cover.png": an <img> with no alt attribute at all (alt="" is right for decoration);
// - "field without label: email": an <input>, <select> or <textarea> that is not hidden (type hidden,
//   submit or button) and has neither a <label> around it nor a <label for="its id">;
// - "link without text: /next": an <a> with no text and no aria-label (an icon alone says nothing).
// A page without problems gives [].

final class AccessibilityCheck
{
    /** @return list<string> */
    public static function problems(string $html): array
    {
        $page = Dom\HTMLDocument::createFromString($html, LIBXML_NOERROR);

        return [
            ...self::language($page),
            ...self::headings($page),
            ...self::images($page),
            ...self::fields($page),
            ...self::links($page),
        ];
    }

    /** @return list<string> */
    private static function language(Dom\HTMLDocument $page): array
    {
        return trim((string) $page->documentElement?->getAttribute('lang')) === '' ? ['no lang'] : [];
    }

    /** @return list<string> */
    private static function headings(Dom\HTMLDocument $page): array
    {
        $problems = [];
        $count = $page->querySelectorAll('h1')->length;
        if ($count !== 1) {
            $problems[] = "h1: $count";
        }
        $before = 0;
        foreach ($page->querySelectorAll('h1, h2, h3, h4, h5, h6') as $heading) {
            $level = (int) substr($heading->localName, 1);
            if ($before > 0 && $level > $before + 1) {
                $problems[] = "heading skips: h$before to h$level";
            }
            $before = $level;
        }

        return $problems;
    }

    /** @return list<string> */
    private static function images(Dom\HTMLDocument $page): array
    {
        $problems = [];
        foreach ($page->querySelectorAll('img:not([alt])') as $image) {
            $problems[] = 'image without alt: ' . $image->getAttribute('src');
        }

        return $problems;
    }

    /** @return list<string> */
    private static function fields(Dom\HTMLDocument $page): array
    {
        $problems = [];
        foreach ($page->querySelectorAll('input:not([type=hidden], [type=submit], [type=button]), select, textarea') as $field) {
            $id = (string) $field->getAttribute('id');
            $labelled = $field->closest('label') !== null
                || $id !== '' && $page->querySelector('label[for="' . addcslashes($id, '"\\') . '"]') !== null;
            if (!$labelled) {
                $problems[] = 'field without label: ' . $field->getAttribute('name');
            }
        }

        return $problems;
    }

    /** @return list<string> */
    private static function links(Dom\HTMLDocument $page): array
    {
        $problems = [];
        foreach ($page->querySelectorAll('a') as $link) {
            if (trim($link->textContent ?? '') === '' && trim((string) $link->getAttribute('aria-label')) === '') {
                $problems[] = 'link without text: ' . $link->getAttribute('href');
            }
        }

        return $problems;
    }
}
