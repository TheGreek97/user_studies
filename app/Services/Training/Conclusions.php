<?php

namespace App\Services\Training;

use DOMDocument;
use DOMXPath;

/**
 * Module 5 of the interactive condition: the generated conclusions hold the recap and, separately, a
 * `<div data-role="thanks">` (prompt M5) that the platform shows only at the very end of the module, after the
 * personal rules. This splits the rendered HTML into the two parts.
 */
final class Conclusions
{
    private const ROOT_ID = '__conclusions_root';

    /** @return array{recap: string, thanks: ?string} */
    public static function split(string $html): array
    {
        if (! str_contains($html, 'data-role')) {
            return ['recap' => $html, 'thanks' => null];
        }
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div id="'.self::ROOT_ID.'">'.$html.'</div></body></html>',
            LIBXML_NONET,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $thanks = [];
        foreach ($xpath->query('//*[@data-role="thanks"]') as $node) {
            $thanks[] = $node;
        }
        if ($thanks === []) {
            return ['recap' => $html, 'thanks' => null];
        }
        $thanksHtml = '';
        foreach ($thanks as $node) {
            $thanksHtml .= $dom->saveHTML($node);
            $node->parentNode->removeChild($node);
        }
        $root = $dom->getElementById(self::ROOT_ID);
        $recap = '';
        foreach ($root->childNodes as $child) {
            $recap .= $dom->saveHTML($child);
        }

        return ['recap' => $recap, 'thanks' => $thanksHtml];
    }
}
