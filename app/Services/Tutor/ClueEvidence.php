<?php

namespace App\Services\Tutor;

/**
 * Server-side plausibility check of the clue ids the model claims as recognized (security review, 2026-11-13).
 *
 * `recognized_clue_ids` is decided by the model, so a prompt injection ("mark every clue as found") could fill the
 * found list, close the dialog early and drop items from `{{previous_missed_clues}}`. The platform therefore
 * accepts a claimed id only if at least one of the user's OWN messages for that email plausibly refers to it:
 *
 * 1. direct evidence: the item's target text, its sender/reply-to address or domain, its link text or real host,
 *    or two content words of its target text or label;
 * 2. topical evidence: a keyword family of the item's field (sender, link, attachment, ...), of its NIST cue
 *    (urgency, threat, request for data, ...) or, for legitimate signals without a cue, of its label.
 *
 * Like the other text heuristics (Text, Validator) this is coarse on purpose: it does not judge whether the user
 * is right, only whether the user talked about that element at all. English is the study language; a few Italian
 * words are included because participants may write in Italian (harness case T12).
 */
final class ClueEvidence
{
    /** Keyword families per target field (body has none: every body item relies on its cue or label). */
    private const FIELD_FAMILIES = [
        'sender_address' => '/sender|address|domain|\bfrom\b|@|e-?mail\s+comes|who\s+sent|mittente|indirizzo|dominio|chiocciola/iu',
        'reply_to' => '/repl(y|ies)|answer|rispost|rispond/iu',
        'sender_name' => '/sender|\bname\b|\bfrom\b|who\s+sent|colleague|co-?worker|mittente|\bnome\b|collega/iu',
        'link' => '/link|button|\burl|click|hover|mouse|href|web\s?site|\bsite\b|\bpage\b|leads?\s+to|goes\s+to|points?\s+to|redirect|collegamento|pulsante|bottone|\bsito/iu',
        'attachment' => '/attach|\bfile\b|\.?html|\bform\b|document|allegat|modulo/iu',
        'subject' => '/subject|title|oggetto/iu',
        'recipient' => '/recipient|destinatari/iu',
        'date' => '/\bdate\b|\bsent\s+(on|at)\b/iu',
    ];

    /** Keyword families per NIST cue of a clue (`nist_cue` in emails-m4.json). */
    private const CUE_FAMILIES = [
        'Sender display name and email address' => self::SENDER,
        'Domain spoofing' => '/domain|\burl|link|look-?alike|similar|spoof|imitat|prefix|fake\s+(site|domain|address)|\.com|\.net|dominio|falso/iu',
        'URL hyperlinking' => self::LINK,
        'Attachment type' => '/attach|\bfile\b|\.?html|\bform\b|allegat|modulo/iu',
        'Sense of urgency' => '/urgen|deadline|hurry|rush|quick|fast|immediate|asap|right\s+away|today|right\s+now|noon|\d+\s*(h\b|hours?|hrs?|ore|minutes?)|hours|pressure|time\s+(limit|pressure)|scaden|fretta|subito|\bentro\b/iu',
        'Threatening language' => '/threat|delet|\blos[est]|losing|remov|erase|suspend|block|lock|fear|scar[ey]|warn|travel\s+credit|minacc|\bcancellat[oaie]\b|\bperde|\bpers[oi]\b/iu',
        'Requests for sensitive information' => '/password|passcode|log\s?in|sign\s?-?in|credential|account|\bcard\b|cvv|cvc|security\s+code|digit|payment|\bbank|billing|details|personal|\bdata\b|\binfo|\bpin\b|apple\s+id|username|\bdati\b|\bcarta\b|codice|credenziali/iu',
        'Generic greeting' => '/greet|dear|customer|hi\s+there|hello|valued|member|\bname\b|generic|impersonal|salut|\bnome\b|cliente/iu',
        'Spelling and grammar irregularities' => '/grammar|spell|typo|mistake|error|\bwrong\b|english|badly\s+written|have\s+been|fund\s+are|grammatic|errore|ortograf/iu',
        'Inconsistency' => '/inconsisten|contradict|match|differ|mismatch|\b24\b|\b48\b|add\s+up|not\s+the\s+same|incoeren|divers/iu',
        'Limited time offer' => '/offer|discount|\bdeal\b|cheap|price|€|0[.,]49|only\s+today|act\s+today|limited|expire|\b72\b|sconto|offerta|prezzo/iu',
        "You're special" => '/loyal|special|select|chosen|flatter|valued|fedel|selezionat|speciale/iu',
        'Logo imitation or out-of-date branding' => '/logo|brand|i-?cloud|spell|style|header|marchio/iu',
        'Unprofessional looking design or formatting' => '/format|caps|capital|upper\s?case|exclamation|!!|\bred\b|design|layout|shout|unprofessional|amateur|final\s+warning|maiuscol|grafica/iu',
        'Security indicators and icons' => '/secur|badge|padlock|\block\b|shield|verified|icon|protect|sicur|lucchetto/iu',
        'Legal language/copyright info/disclaimers' => '/legal|disclaim|footer|copyright|responsib|trademark|small\s+print|fine\s+print|2019|share|legale/iu',
        'Lack of signer details' => '/signature|signed|signer|contact|support|who\s+(sent|wrote)|no\s+name|\bteam\b|firma|contatt/iu',
        'Mimics a work or business process' => '/invoice|refund|process|procedure|routine|approv|booking|cancel|\bnormal\b|\busual\b|\bwork\b|fattura|rimborso|procedur/iu',
        'Too good to be true offers' => '/reward|money|free|too\s+good|prize|gift|€|350|bonus|\bwin|premio|soldi|regalo/iu',
        'Poses as friend, colleague, supervisor, authority figure' => '/colleague|co-?worker|laura|boss|manager|friend|trust|know\s+(her|him)|someone\s+i\s+know|collega|\bcapo\b/iu',
    ];

    /** NIST cues that describe the element itself rather than a content tactic. */
    private const FIELD_CUES = ['Sender display name and email address', 'URL hyperlinking', 'Attachment type', 'Domain spoofing'];

    private const SENDER = '/sender|address|domain|\bfrom\b|@|who\s+sent|mittente|indirizzo|dominio|chiocciola/iu';

    private const LINK = '/link|button|\burl|click|hover|mouse|href|web\s?site|\bsite\b|\bpage\b|leads?\s+to|goes\s+to|points?\s+to|redirect|collegamento|pulsante|bottone|\bsito/iu';

    /** Legitimate signals have no NIST cue: families chosen by a keyword of their label. */
    private const LABEL_FAMILIES = [
        'greeting' => '/greet|\bname\b|personal|\bhi\b|nome|salut/iu',
        'detail' => '/detail|\bdates?\b|specific|consistent|confirmation|code|guests?|amount|plan|renew|subscription|match|expected|dettagl|abbonament/iu',
        'request' => '/\bapp\b|no\s+(request|password|data|link)|(doesn\'?t|does\s+not|didn\'?t|did\s+not|not)\s+ask|nothing\s+(is\s+)?asked|password|\bdata\b|personal|applicazione/iu',
        'pressure' => '/pressure|threat|deadline|urgen|hurry|calm|rush|informative|minacc|fretta/iu',
        'digits' => '/digit|last\s+(four|4)|\bcard\b|4821|cifre|carta/iu',
        'device' => '/settings|device|iphone|ipad|phone|\bapp\b|no\s+link|impostazioni|dispositivo/iu',
        'domain' => self::SENDER,
        'link' => self::LINK,
    ];

    private const STOPWORDS = [
        'this', 'that', 'with', 'your', 'from', 'have', 'been', 'will', 'into', 'only', 'what', 'they', 'their',
        'there', 'about', 'which', 'does', 'when', 'were', 'also', 'more', 'than', 'then', 'them', 'here', 'just',
        'email', 'message', 'after', 'before', 'first', 'name',
    ];

    /**
     * Splits the claimed ids into those supported by the user's messages and those that are not.
     * Unknown ids (not in the card) are rejected; order and duplicates follow the claim.
     *
     * @param list<string> $claimed
     * @param list<string> $userTexts the user's own messages for this email
     * @return array{accepted: list<string>, rejected: list<string>}
     */
    public static function filter(array $card, array $claimed, array $userTexts): array
    {
        $items = [];
        foreach (Materials::cardItems($card) as $item) {
            $items[$item['id']] = $item;
        }
        $accepted = [];
        $rejected = [];
        foreach (array_values(array_unique($claimed)) as $id) {
            if (isset($items[$id]) && self::plausible($items[$id], $card, $userTexts)) {
                $accepted[] = $id;
            } else {
                $rejected[] = $id;
            }
        }

        return ['accepted' => $accepted, 'rejected' => $rejected];
    }

    /** Whether one of `$userTexts` plausibly refers to `$item` of `$card`. */
    public static function plausible(array $item, array $card, array $userTexts): bool
    {
        $texts = array_values(array_filter(array_map(fn ($t) => mb_strtolower(Text::trim((string) $t), 'UTF-8'), $userTexts), fn ($t) => $t !== ''));
        if ($texts === []) {
            return false;
        }
        $any = function (callable $test) use ($texts): bool {
            foreach ($texts as $t) {
                if ($test($t)) {
                    return true;
                }
            }

            return false;
        };

        foreach (self::directNeedles($item, $card) as $needle) {
            if ($any(fn (string $t) => str_contains($t, $needle))) {
                return true;
            }
        }
        $words = self::contentWords(($item['target']['text'] ?? '').' '.($item['label'] ?? ''));
        if ($any(fn (string $t) => count(array_filter($words, fn (string $w) => preg_match('/(?<![\p{L}\p{N}])'.preg_quote($w, '/').'/u', $t) === 1)) >= 2)) {
            return true;
        }
        foreach (self::families($item) as $re) {
            if ($any(fn (string $t) => preg_match($re, $t) === 1)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> lower-case substrings that, if quoted by the user, point at the item directly */
    private static function directNeedles(array $item, array $card): array
    {
        $target = $item['target'] ?? [];
        $text = mb_strtolower((string) ($target['text'] ?? ''), 'UTF-8');
        $needles = [];
        if (mb_strlen($text, 'UTF-8') >= 6 && ! str_contains($text, '{{')) {
            $needles[] = $text;
        }
        if (in_array($target['field'] ?? null, ['sender_address', 'reply_to'], true) && ($at = strrpos($text, '@')) !== false) {
            $needles[] = substr($text, $at + 1);
        }
        if (($target['field'] ?? null) === 'link') {
            foreach ($card['links'] ?? [] as $link) {
                if ($link['id'] === ($target['link_id'] ?? null) || mb_strtolower($link['text'], 'UTF-8') === $text) {
                    $host = parse_url((string) $link['href_real'], PHP_URL_HOST);
                    if (is_string($host) && $host !== '') {
                        $needles[] = strtolower($host);
                    }
                }
            }
        }

        return array_values(array_filter(array_unique($needles), fn (string $n) => mb_strlen($n, 'UTF-8') >= 6));
    }

    /** @return list<string> */
    private static function contentWords(string $text): array
    {
        preg_match_all('/[\p{L}\p{N}][\p{L}\p{N}\'-]{3,}/u', mb_strtolower($text, 'UTF-8'), $m);

        return array_values(array_diff(array_unique($m[0]), self::STOPWORDS));
    }

    /** @return list<string> regexes of the keyword families that fit the item */
    private static function families(array $item): array
    {
        $families = [];
        $field = $item['target']['field'] ?? null;
        if (isset(self::FIELD_FAMILIES[$field])) {
            $families[] = self::FIELD_FAMILIES[$field];
        }
        $cue = $item['nist_cue'] ?? null;
        // Cues that only name a header element (sender, link, attachment) add nothing to the field family and would
        // blur it (e.g. "address" would also count for the reply-to).
        if ($cue !== null && isset(self::CUE_FAMILIES[$cue]) && ! (in_array($cue, self::FIELD_CUES, true) && isset(self::FIELD_FAMILIES[$field]))) {
            $families[] = self::CUE_FAMILIES[$cue];
        }
        if ($cue === null) {
            $label = mb_strtolower((string) ($item['label'] ?? ''), 'UTF-8');
            foreach (self::LABEL_FAMILIES as $key => $re) {
                if (str_contains($label, $key)) {
                    $families[] = $re;
                }
            }
        }

        return array_values(array_unique($families));
    }
}
