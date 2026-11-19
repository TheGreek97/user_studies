<?php

namespace App\Services\Training;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * DOM helpers for HTML fragments written by the model (UTF-8, possibly malformed): parse into a document whose
 * <body> holds the fragment, serialise the children of a node back to HTML.
 */
final class HtmlFragment
{
    /** @return array{0: DOMDocument, 1: DOMElement} document and its <body> (the fragment root) */
    public static function load(string $html): array
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        // The http-equiv meta makes libxml read the fragment as UTF-8 (no mb_convert_encoding HTML-ENTITIES,
        // deprecated in PHP 8.2).
        $doc->loadHTML(
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>'
            . $html . '</body></html>',
            LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body instanceof DOMElement) {  // only for an empty document
            $body = $doc->createElement('body');
            $doc->appendChild($body);
        }

        return [$doc, $body];
    }

    public static function innerHtml(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    public static function outerHtml(DOMNode $node): string
    {
        return $node->ownerDocument->saveHTML($node);
    }

    /** Text of a node with collapsed whitespace. */
    public static function text(DOMNode $node): string
    {
        return trim(preg_replace('/\s+/u', ' ', $node->textContent));
    }
}
