<?php

namespace App\Services\Llm;

/**
 * Reduces a JSON Schema to the subset accepted by Anthropic structured outputs
 * (output_config.format = json_schema).
 *
 * The API rejects numeric/length constraints, most array constraints, `$schema`/`$id`
 * and any `additionalProperties` other than false. This class strips those keywords
 * recursively and forces `additionalProperties: false` on every object. Removed
 * constraints are appended to the `description` as a hint for the model.
 *
 * The caller MUST re-validate the model output against the original (full) schema:
 * the sanitized schema is looser than the original one.
 */
final class JsonSchemaSanitizer
{
    /** Keywords removed (and reported in the description as a hint). */
    private const UNSUPPORTED_CONSTRAINTS = [
        'minLength', 'maxLength', 'pattern',
        'minimum', 'maximum', 'exclusiveMinimum', 'exclusiveMaximum', 'multipleOf',
        'maxItems', 'uniqueItems', 'contains', 'minContains', 'maxContains',
        'minProperties', 'maxProperties', 'patternProperties', 'propertyNames',
        'dependentRequired', 'dependentSchemas', 'if', 'then', 'else', 'not',
    ];

    /** Keywords removed silently (metadata with no meaning for generation). */
    private const DROPPED_METADATA = ['$schema', '$id', '$comment', 'examples', 'readOnly', 'writeOnly', 'deprecated'];

    /** String formats supported by structured outputs; any other `format` is dropped. */
    private const SUPPORTED_FORMATS = ['date-time', 'time', 'date', 'duration', 'email', 'hostname', 'uri', 'ipv4', 'ipv6', 'uuid'];

    /** Keywords whose value is a map name => schema. */
    private const SCHEMA_MAPS = ['properties', '$defs', 'definitions'];

    /** Keywords whose value is a list of schemas. */
    private const SCHEMA_LISTS = ['anyOf', 'allOf', 'prefixItems'];

    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    public static function sanitize(array $schema): array
    {
        $removed = [];
        $out = [];

        foreach ($schema as $key => $value) {
            if (in_array($key, self::DROPPED_METADATA, true)) {
                continue;
            }
            if (in_array($key, self::UNSUPPORTED_CONSTRAINTS, true)) {
                // Sub-schemas (not/if/then/...) are not useful as hints; scalars are.
                if (!is_array($value)) {
                    $removed[$key] = $value;
                }
                continue;
            }

            switch ($key) {
                case 'minItems':
                    // Only 0 and 1 are accepted by the API.
                    if (is_int($value) && $value <= 1) {
                        $out[$key] = $value;
                    } else {
                        $removed[$key] = $value;
                    }
                    break;

                case 'format':
                    if (is_string($value) && in_array($value, self::SUPPORTED_FORMATS, true)) {
                        $out[$key] = $value;
                    } else {
                        $removed[$key] = $value;
                    }
                    break;

                case 'oneOf':
                    // oneOf is not supported; anyOf is the closest accepted keyword.
                    $out['anyOf'] = self::sanitizeList($value);
                    break;

                case 'items':
                    // Draft 4 tuple form (list of schemas) is converted to prefixItems.
                    if (is_array($value) && array_is_list($value) && $value !== []) {
                        $out['prefixItems'] = self::sanitizeList($value);
                    } elseif (is_array($value)) {
                        $out['items'] = self::sanitize($value);
                    } else {
                        $out['items'] = $value;
                    }
                    break;

                case 'additionalProperties':
                    // Forced to false below for every object.
                    break;

                default:
                    if (in_array($key, self::SCHEMA_MAPS, true) && is_array($value)) {
                        $out[$key] = array_map(
                            static fn ($sub) => is_array($sub) ? self::sanitize($sub) : $sub,
                            $value
                        );
                    } elseif (in_array($key, self::SCHEMA_LISTS, true) && is_array($value)) {
                        $out[$key] = self::sanitizeList($value);
                    } else {
                        $out[$key] = $value;
                    }
            }
        }

        if (self::isObjectSchema($out)) {
            $out['additionalProperties'] = false;
        }

        if ($removed !== []) {
            $hint = json_encode($removed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $out['description'] = isset($out['description']) && is_string($out['description'])
                ? $out['description'] . "\n\n{" . trim($hint, '{}') . '}'
                : $hint;
        }

        return $out;
    }

    /**
     * @param array<int, mixed> $schemas
     * @return array<int, mixed>
     */
    private static function sanitizeList(array $schemas): array
    {
        return array_map(static fn ($sub) => is_array($sub) ? self::sanitize($sub) : $sub, array_values($schemas));
    }

    /**
     * @param array<string, mixed> $schema
     */
    private static function isObjectSchema(array $schema): bool
    {
        $type = $schema['type'] ?? null;

        return $type === 'object'
            || (is_array($type) && in_array('object', $type, true))
            || (!isset($type) && isset($schema['properties']));
    }
}
