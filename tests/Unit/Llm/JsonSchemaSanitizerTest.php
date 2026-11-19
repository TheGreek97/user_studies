<?php

namespace Tests\Unit\Llm;

use App\Services\Llm\JsonSchemaSanitizer;
use PHPUnit\Framework\TestCase;

class JsonSchemaSanitizerTest extends TestCase
{
    public function test_strips_unsupported_keywords_recursively_and_closes_objects(): void
    {
        $schema = [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            '$id' => 'urn:test',
            'type' => 'object',
            'additionalProperties' => true,
            'required' => ['title', 'items'],
            'properties' => [
                'title' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 80, 'pattern' => '^[A-Z]'],
                'score' => ['type' => 'number', 'minimum' => 0, 'maximum' => 10, 'multipleOf' => 0.5],
                'email' => ['type' => 'string', 'format' => 'email'],
                'color' => ['type' => 'string', 'format' => 'hex-color'],
                'items' => [
                    'type' => 'array',
                    'minItems' => 3,
                    'maxItems' => 5,
                    'uniqueItems' => true,
                    'items' => [
                        'type' => 'object',
                        'properties' => ['id' => ['type' => 'integer', 'exclusiveMinimum' => 0]],
                        'additionalProperties' => ['type' => 'string'],
                    ],
                ],
                'choice' => ['oneOf' => [['type' => 'string', 'maxLength' => 2], ['type' => 'object', 'properties' => []]]],
            ],
        ];

        $out = JsonSchemaSanitizer::sanitize($schema);
        $json = json_encode($out);

        foreach (['$schema', '$id', 'minLength', 'maxLength', 'pattern', 'minimum', 'maximum', 'multipleOf',
                     'exclusiveMinimum', 'maxItems', 'uniqueItems', 'oneOf', 'hex-color'] as $keyword) {
            $this->assertArrayNotHasKeyRecursive($keyword, $out);
        }
        $this->assertFalse($out['additionalProperties']);
        $this->assertSame(['title', 'items'], $out['required']);
        $this->assertSame('email', $out['properties']['email']['format']);
        $this->assertArrayNotHasKey('format', $out['properties']['color']);
        $this->assertArrayNotHasKey('minItems', $out['properties']['items']);
        $this->assertFalse($out['properties']['items']['items']['additionalProperties']);
        $this->assertSame('integer', $out['properties']['items']['items']['properties']['id']['type']);
        $this->assertCount(2, $out['properties']['choice']['anyOf']);
        $this->assertFalse($out['properties']['choice']['anyOf'][1]['additionalProperties']);
        // Removed constraints survive as a description hint.
        $this->assertStringContainsString('"minLength":3', $out['properties']['title']['description']);
        $this->assertNotFalse($json);
    }

    public function test_keeps_supported_min_items_and_existing_description(): void
    {
        $out = JsonSchemaSanitizer::sanitize([
            'type' => 'array',
            'minItems' => 1,
            'items' => ['type' => 'string', 'description' => 'A tip', 'maxLength' => 100],
        ]);

        $this->assertSame(1, $out['minItems']);
        $this->assertStringStartsWith('A tip', $out['items']['description']);
        $this->assertStringContainsString('"maxLength":100', $out['items']['description']);
        $this->assertArrayNotHasKey('additionalProperties', $out);
    }

    public function test_property_names_matching_keywords_are_not_stripped(): void
    {
        $out = JsonSchemaSanitizer::sanitize([
            'type' => 'object',
            'properties' => ['pattern' => ['type' => 'string'], 'minimum' => ['type' => 'integer']],
        ]);

        $this->assertSame(['pattern', 'minimum'], array_keys($out['properties']));
    }

    private function assertArrayNotHasKeyRecursive(string $needle, array $haystack): void
    {
        array_walk_recursive($haystack, function ($value, $key) use ($needle) {
            $this->assertNotSame($needle, $key, "Keyword $needle still present");
            $this->assertNotSame($needle, $value, "Value $needle still present");
        });
        foreach ($haystack as $key => $value) {
            if ($key === 'properties') {
                continue;  // property names are data, not keywords
            }
            $this->assertNotSame($needle, $key, "Keyword $needle still present");
            if (is_array($value)) {
                $this->assertArrayNotHasKeyRecursive($needle, $value);
            }
        }
    }
}
