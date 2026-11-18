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
        return []; // TODO
    }
}
