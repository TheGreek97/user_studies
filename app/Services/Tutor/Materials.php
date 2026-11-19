<?php

namespace App\Services\Tutor;

use InvalidArgumentException;
use RuntimeException;

/**
 * Loading of the Module 4 materials (port of tutor-core `src/materials.ts`).
 *
 * The materials (email bank, system prompt, output schema, test dialogs) are read at runtime from
 * `config('tutor.materials_dir')` (default `resources/tutor`), never copied into the code, so an edit
 * to the bank or the prompt is picked up without changing the core.
 *
 * Cards are kept as decoded associative arrays (like the TS objects): only the fields the core uses are
 * read, the rest travels untouched to the prompt.
 */
class Materials
{
    public const EMAIL_BANK = 'emails-m4.json';
    public const OUTPUT_SCHEMA = 'tutor-output-schema.json';
    public const PROMPT = 'tutor-system-prompt.md';
    public const TEST_DIALOGS = 'test-dialogs.md';

    public readonly string $dir;

    public function __construct(?string $dir = null)
    {
        $this->dir = rtrim($dir ?? self::defaultDir(), '/');
    }

    /** `config('tutor.materials_dir')` inside the app; `resources/tutor` of this repo in plain unit tests. */
    public static function defaultDir(): string
    {
        if (function_exists('app') && app()->bound('config')) {
            $dir = config('tutor.materials_dir');
            if (is_string($dir) && $dir !== '') {
                return $dir;
            }
        }

        return dirname(__DIR__, 3).'/resources/tutor';
    }

    public function path(string $name): string
    {
        return $this->dir.'/'.$name;
    }

    private function read(string $name): string
    {
        $path = $this->path($name);
        $text = @file_get_contents($path);
        if ($text === false) {
            throw new RuntimeException("Tutor material not found: {$path}");
        }

        return $text;
    }

    /** @return array{_meta: array<string, mixed>, emails: list<array<string, mixed>>} */
    public function loadEmailBank(): array
    {
        return json_decode($this->read(self::EMAIL_BANK), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @return array<string, mixed> */
    public function loadOutputSchema(): array
    {
        return json_decode($this->read(self::OUTPUT_SCHEMA), true, 512, JSON_THROW_ON_ERROR);
    }

    public function loadPromptMarkdown(): string
    {
        return $this->read(self::PROMPT);
    }

    public function loadTestDialogsMarkdown(): string
    {
        return $this->read(self::TEST_DIALOGS);
    }

    /** @return array<string, mixed> */
    public static function findCard(array $bank, string $id): array
    {
        foreach ($bank['emails'] as $card) {
            if ($card['id'] === $id) {
                return $card;
            }
        }
        throw new InvalidArgumentException("Email card not found: {$id}");
    }

    /**
     * Clues of a phishing email or signs of authenticity of a legitimate one (the ids the tutor may use, G3).
     *
     * @return list<array<string, mixed>>
     */
    public static function cardItems(array $card): array
    {
        return array_values(array_merge($card['clues'] ?? [], $card['legit_signals'] ?? []));
    }

    /** @return list<string> */
    public static function cardIds(array $card): array
    {
        return array_map(fn (array $i) => $i['id'], self::cardItems($card));
    }

    /** @return list<string> */
    public static function mainIds(array $card): array
    {
        return array_values(array_map(
            fn (array $i) => $i['id'],
            array_filter(self::cardItems($card), fn (array $i) => ! empty($i['main'])),
        ));
    }
}
