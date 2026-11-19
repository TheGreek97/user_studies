<?php

namespace App\Services\Training;

use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Splits the generated Module 2 HTML (interactive condition) around its `<div data-role="decision-point">`, so the
 * platform can put the annotated email before it and show the decision point only after all clues are opened.
 *
 * The tree is cut at the decision point: `before` keeps what precedes it in document order, `after` what follows,
 * both as balanced HTML (the ancestors of the decision point appear in both parts; their id is dropped from
 * `after` to avoid duplicates). The radio buttons of the decision point are read here, so the server knows the
 * valid answers and their labels: the browser only sends the index of the chosen radio.
 */
final class ScenarioSection
{
    public const MAX_LABEL = 200;

    /**
     * @return array{before: string, decision: ?string, after: string, options: list<array{value: string, label: string}>}
     */
    public static function split(string $html): array
    {
        [$doc, $body] = HtmlFragment::load($html);
        $target = (new DOMXPath($doc))->query('//*[@data-role="decision-point"]')->item(0);
        if (! $target instanceof DOMElement) {
            return ['before' => HtmlFragment::innerHtml($body), 'decision' => null, 'after' => '', 'options' => []];
        }

        $path = self::path($body, $target);
        $options = self::options($target);
        $decision = HtmlFragment::outerHtml($target);

        $before = $body->cloneNode(true);
        self::cut($before, $path, false);
        $after = $body->cloneNode(true);
        self::cut($after, $path, true);

        return [
            'before' => HtmlFragment::innerHtml($before),
            'decision' => $decision,
            'after' => HtmlFragment::innerHtml($after),
            'options' => $options,
        ];
    }

    /** @return list<int> child indexes from the root to the node */
    private static function path(DOMNode $root, DOMNode $node): array
    {
        $path = [];
        while ($node !== $root) {
            $index = 0;
            for ($sibling = $node->previousSibling; $sibling !== null; $sibling = $sibling->previousSibling) {
                $index++;
            }
            array_unshift($path, $index);
            $node = $node->parentNode;
        }

        return $path;
    }

    /**
     * Removes the node at $path and everything after it ($keepAfter false) or before it ($keepAfter true),
     * at every level of the tree.
     */
    private static function cut(DOMNode $root, array $path, bool $keepAfter): void
    {
        $node = $root;
        foreach ($path as $depth => $index) {
            $child = $node->childNodes->item($index);
            $isTarget = $depth === count($path) - 1;
            if ($keepAfter) {
                while ($child->previousSibling !== null) {
                    $node->removeChild($child->previousSibling);
                }
                if ($child instanceof DOMElement && ! $isTarget) {
                    $child->removeAttribute('id');  // ancestor already printed in the "before" part
                }
            } else {
                while ($child->nextSibling !== null) {
                    $node->removeChild($child->nextSibling);
                }
            }
            if ($isTarget) {
                $node->removeChild($child);
            }
            $node = $child;
        }
    }

    /** @return list<array{value: string, label: string}> radios of the decision point, in document order */
    private static function options(DOMElement $decision): array
    {
        $xpath = new DOMXPath($decision->ownerDocument);
        $options = [];
        foreach ($xpath->query('.//input', $decision) as $input) {
            if (strtolower(trim($input->getAttribute('type'))) !== 'radio') {
                continue;
            }
            $value = $input->hasAttribute('value') ? $input->getAttribute('value') : 'on';
            $options[] = ['value' => mb_substr($value, 0, 100), 'label' => mb_substr(self::label($input, $xpath), 0, self::MAX_LABEL)];
        }

        return $options;
    }

    /** Label of a radio: enclosing <label>, <label for=id>, or the text that follows it up to the next control. */
    private static function label(DOMElement $input, DOMXPath $xpath): string
    {
        for ($node = $input->parentNode; $node instanceof DOMElement; $node = $node->parentNode) {
            if (strtolower($node->nodeName) === 'label') {
                return HtmlFragment::text($node);
            }
        }
        $id = $input->getAttribute('id');
        if ($id !== '') {
            foreach ($xpath->query('//label') as $label) {
                if ($label->getAttribute('for') === $id) {
                    return HtmlFragment::text($label);
                }
            }
        }
        $text = '';
        for ($node = $input->nextSibling; $node !== null; $node = $node->nextSibling) {
            if ($node instanceof DOMElement && in_array(strtolower($node->nodeName), ['input', 'br', 'button', 'p', 'div'], true)) {
                break;
            }
            $text .= ' ' . $node->textContent;
        }

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
