<?php

namespace App\Services\Tutor;

use App\Services\Llm\LlmClient;
use App\Services\Llm\LlmException;
use App\Services\Llm\LlmResponse;

/**
 * Offline tutor for LOCAL DEMOS ONLY (bound to "tutor.llm" when TUTOR_FAKE=true and APP_ENV=local, see
 * AppServiceProvider::fakeTutorEnabled). Never calls the network.
 *
 * Unlike FakeLlmClient (fixed script), it reads the built prompt (card, turn type, dialog history) and writes a
 * plausible reply that passes the Validator: clue ids recognised by keyword matching on what the user wrote,
 * generic Socratic messages (max 3 sentences, one question, no verdict, no zone hints, no card internals).
 * It shows the whole UI flow; it is not a model and its pedagogy is canned.
 *
 * It also answers the Module 5 comment on the personal rules (prompt 5b, PersonalRules): a missed cue counts as
 * covered when the rules mention it (same keyword matching), the question goes to the last uncovered one.
 */
class DemoLlmClient implements LlmClient
{
    public const MODEL = 'demo-fake-tutor';

    /** Words that point to an item, by target field of the card (matched on the user's own words only). */
    private const FIELD_KEYWORDS = [
        'sender_name' => ['sender', 'name', 'from'],
        'sender_address' => ['sender', 'address', 'domain', '@', 'from'],
        'reply_to' => ['reply'],
        'subject' => ['subject', 'title'],
        'link' => ['link', 'button', 'url', 'hover', 'click', 'site', 'website'],
        'attachment' => ['attach', 'file', 'document'],
        'body' => [],
    ];

    private const STOPWORDS = ['email', 'message', 'sender', 'there', 'their', 'which', 'about', 'other', 'every'];

    /** @var list<array<string, mixed>>|null */
    private ?array $cards = null;

    public function __construct(private readonly ?Materials $materials = null, private readonly int $delayMs = 0)
    {
    }

    public function complete(string $system, array $messages, array $options = []): LlmResponse
    {
        if ($this->delayMs > 0) {
            usleep($this->delayMs * 1000);  // lets the "tutor is writing" state show in the UI
        }
        if (str_contains($system, "# 6. USER'S RULES")) {
            return $this->rulesComment($system);
        }
        $card = $this->cardFromPrompt($system);
        $closing = preg_match('/Current turn type:\s*closing\b/', $system) === 1;
        $userTexts = array_values(array_map(
            fn (array $t) => (string) ($t['text'] ?? ''),
            array_filter(self::historyFromPrompt($system), fn ($t) => is_array($t) && ($t['author'] ?? '') === 'user'),
        ));
        $last = (string) (end($userTexts) ?: '');

        $ids = Materials::cardIds($card);
        $recognized = array_values(array_filter($ids, fn (string $id) => $this->mentioned(self::itemById($card, $id), $userTexts)));
        $missing = array_values(array_diff($ids, $recognized));
        $mainMissing = array_values(array_intersect(Materials::mainIds($card), $missing));
        $dontKnow = preg_match("/\\b(don'?t know|no idea|not sure|dunno)\\b/i", $last) === 1;
        $minimal = mb_strlen(trim($last), 'UTF-8') < 15;
        $askedAnswer = preg_match('/\b(tell me|what is the answer|is it phishing|just say)\b/i', $last) === 1;
        $newHere = array_values(array_filter($ids, fn (string $id) => $this->mentioned(self::itemById($card, $id), [$last])));

        $message = match (true) {
            $closing => 'Thanks for working through this email with me. The summary shows the details you found and the ones worth a second look next time. Keep applying the same calm checks to every message you receive.',
            $askedAnswer => 'I would rather let you work it out, and you will see the answer at the end. Which detail of the email made you hesitate the most?',
            $dontKnow => 'No problem, let us take it one step at a time. Read the email again calmly: what is the first thing that feels unusual to you?',
            $newHere !== [] => 'Good observation, that detail deserves attention. What else in this email would you check before acting on it?',
            default => 'Thanks for explaining your reasoning. Which concrete detail in the email supports what you just said?',
        };

        $reply = [
            'message' => $message,
            'recognized_clue_ids' => $recognized,
            'missing_clue_ids' => $missing,
            'asked_for_answer' => ! $closing && $askedAnswer,
            'propose_close' => false,
            'focus_clue_id' => $closing ? null : ($mainMissing[0] ?? null),
            'user_said_dont_know' => ! $closing && $dontKnow,
            'minimal_reply' => ! $closing && $minimal,
            'unsupported_claims' => [],
            'off_topic' => false,
        ];

        return new LlmResponse(json_encode($reply, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), self::MODEL, 'end_turn', ['input_tokens' => 0, 'output_tokens' => 0]);
    }

    /** Module 5 (prompt 5b): valid comment JSON built from blocks 3 (missed cues) and 6 (the user's rules). */
    private function rulesComment(string $system): LlmResponse
    {
        $missed = [];
        if (preg_match('/# 3\. CUES MISSED IN THE EXERCISES[^\n]*\n(.*?)\n\n# 4\./s', $system, $m) === 1) {
            $decoded = json_decode($m[1], true);
            foreach (is_array($decoded) ? $decoded : [] as $exercise) {
                foreach ($exercise['missed_main_clues'] ?? [] as $clue) {
                    $missed[] = ['id' => (string) $clue['id'], 'label' => (string) ($clue['label'] ?? ''), 'target' => (array) ($clue['position'] ?? [])];
                }
            }
        }
        $rules = [];
        if (preg_match('/# 6\. USER\'S RULES\n(.*?)\n\n# 7\./s', $system, $m) === 1) {
            $decoded = json_decode($m[1], true);
            $rules = is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
        }
        $covered = array_values(array_map(fn (array $c) => $c['id'], array_filter($missed, fn (array $c) => $this->mentioned($c, $rules))));
        $uncovered = array_values(array_filter($missed, fn (array $c) => ! in_array($c['id'], $covered, true)));
        $focus = $uncovered === [] ? null : end($uncovered);

        $message = match (true) {
            $missed === [] => 'Your rules are concrete and you did not miss any of the key details in the exercises. Keep them in mind every time an email asks you to act.',
            $focus === null => 'Your rules already cover the details that slipped past you in the exercises. Keep them in mind every time an email asks you to act.',
            default => 'Thanks, your rules say clearly what you will do. In one exercise a detail slipped past you: "'.rtrim($focus['label'], '.').'". Which situation in your rules would make you check it next time?',
        };
        $reply = [
            'message' => $message,
            'covered_clue_ids' => $covered,
            'focus_clue_id' => $focus['id'] ?? null,
            'generic_rule' => false,
        ];

        return new LlmResponse(json_encode($reply, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), self::MODEL, 'end_turn', ['input_tokens' => 0, 'output_tokens' => 0]);
    }

    /** Card of the bank whose id opens the email JSON of the prompt (block 3). */
    private function cardFromPrompt(string $system): array
    {
        $this->cards ??= ($this->materials ?? new Materials())->loadEmailBank()['emails'];
        if (preg_match('/"id":\s*"([^"]+)"/', $system, $m) === 1) {
            foreach ($this->cards as $card) {
                if ($card['id'] === $m[1]) {
                    return $card;
                }
            }
        }
        throw new LlmException('DemoLlmClient: no email card found in the prompt');
    }

    /** Dialog history (block 7): the JSON array printed right after its heading line. */
    private static function historyFromPrompt(string $system): array
    {
        if (preg_match('/can change at any time\):\n(\[.*?\n\]|\[\])/s', $system, $m) !== 1) {
            return [];
        }
        $history = json_decode($m[1], true);

        return is_array($history) ? $history : [];
    }

    private static function itemById(array $card, string $id): array
    {
        foreach (Materials::cardItems($card) as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        return [];
    }

    /** @param list<string> $texts */
    private function mentioned(array $item, array $texts): bool
    {
        $keywords = self::FIELD_KEYWORDS[$item['target']['field'] ?? 'body'] ?? [];
        foreach (preg_split('/[^\p{L}]+/u', mb_strtolower((string) ($item['label'] ?? ''), 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) as $word) {
            if (mb_strlen($word, 'UTF-8') >= 5 && ! in_array($word, self::STOPWORDS, true)) {
                $keywords[] = mb_substr($word, 0, 5, 'UTF-8');  // crude stem: "urgency" ~ "urgent"
            }
        }
        foreach ($texts as $text) {
            $lower = mb_strtolower($text, 'UTF-8');
            foreach (array_unique($keywords) as $k) {
                if (str_contains($lower, $k)) {
                    return true;
                }
            }
        }

        return false;
    }
}
