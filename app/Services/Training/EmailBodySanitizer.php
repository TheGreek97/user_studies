<?php

namespace App\Services\Training;

use DOMElement;
use DOMNode;
use DOMText;

/**
 * Allowlist sanitizer for the `body_html` of the generated scenario email (Module 2, interactive condition).
 *
 * The body is model output shown inside the platform page, so only plain formatting survives:
 * - allowed tags keep at most a few safe attributes (no style, class, id, event handlers, URLs);
 * - dangerous elements are removed with their content (script, style, iframe, forms controls, media, svg...);
 * - any other tag is unwrapped (its text stays);
 * - links never navigate: <a> loses its href and gets role="link", tabindex="0" and data-href with the real
 *   destination (links[].href_real of the email, or the href written by the model), also as title (tooltip), shown
 *   on hover/focus;
 * - the {{first_name}} placeholder is replaced in text nodes (the DOM escapes the name when serialised).
 */
final class EmailBodySanitizer
{
    private const ALLOWED = [
        'p', 'br', 'a', 'b', 'strong', 'i', 'em', 'u', 's', 'small', 'span', 'div', 'ul', 'ol', 'li',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'hr', 'pre', 'code', 'center',
    ];

    /** Removed together with their content. */
    private const DROP = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'noscript', 'template',
        'svg', 'math', 'img', 'picture', 'video', 'audio', 'source', 'track', 'canvas', 'map', 'area',
        'input', 'button', 'select', 'textarea', 'option', 'optgroup', 'datalist', 'output',
        'link', 'meta', 'base', 'head', 'title', 'dialog', 'portal',
    ];

    private const LINK_ID = '/^[A-Za-z0-9_-]{1,64}$/';

    private const MAX_HREF = 500;

    /**
     * @param list<array{id: string, text: string, href_real: string}> $links links[] of the scenario email
     */
    public static function sanitize(string $html, array $links, string $firstName): string
    {
        [, $body] = HtmlFragment::load($html);
        self::clean($body, self::hrefById($links));
        self::fillName($body, $firstName);

        return HtmlFragment::innerHtml($body);
    }

    /** @return array<string, string> */
    private static function hrefById(array $links): array
    {
        $map = [];
        foreach ($links as $link) {
            if (is_array($link) && is_string($link['id'] ?? null) && is_string($link['href_real'] ?? null)) {
                $map[$link['id']] = $link['href_real'];
            }
        }

        return $map;
    }

    private static function clean(DOMNode $node, array $hrefs): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;  // also DOMCdataSection, kept as text
            }
            if (! $child instanceof DOMElement) {
                $node->removeChild($child);  // comments, processing instructions
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);
                continue;
            }
            self::clean($child, $hrefs);
            if (! in_array($tag, self::ALLOWED, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            self::filterAttributes($child, $tag, $hrefs);
        }
    }

    private static function filterAttributes(DOMElement $el, string $tag, array $hrefs): void
    {
        $keep = [];
        if ($tag === 'a') {
            $id = $el->getAttribute('data-link-id');
            $validId = preg_match(self::LINK_ID, $id) === 1;
            $href = $validId && isset($hrefs[$id]) ? $hrefs[$id] : trim($el->getAttribute('href'));
            if ($validId) {
                $keep['data-link-id'] = $id;
            }
            $keep['role'] = 'link';
            $keep['tabindex'] = '0';
            $keep['data-href'] = mb_substr($href, 0, self::MAX_HREF);
            if ($keep['data-href'] !== '') {
                $keep['title'] = $keep['data-href'];  // native tooltip on hover, accessible description on focus
            }
        } elseif ($tag === 'td' || $tag === 'th') {
            foreach (['colspan', 'rowspan'] as $name) {
                $value = $el->getAttribute($name);
                if (preg_match('/^[1-9][0-9]?$/', $value) === 1) {
                    $keep[$name] = $value;
                }
            }
        }

        foreach (iterator_to_array($el->attributes) as $attribute) {
            $el->removeAttribute($attribute->nodeName);
        }
        foreach ($keep as $name => $value) {
            $el->setAttribute($name, $value);
        }
    }

    private static function fillName(DOMNode $node, string $firstName): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                if (str_contains($child->data, FirstName::PLACEHOLDER)) {
                    $child->data = FirstName::fillText($child->data, $firstName);
                }
            } elseif ($child->hasChildNodes()) {
                self::fillName($child, $firstName);
            }
        }
    }
}
