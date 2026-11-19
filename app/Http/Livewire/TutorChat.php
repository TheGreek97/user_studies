<?php

namespace App\Http\Livewire;

use App\Services\Training\FirstName;
use App\Services\Tutor\ChatSubmitResult;
use App\Services\Tutor\Classification;
use App\Services\Tutor\Phase;
use App\Services\Tutor\TutorConversation;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Module 4 interactive tutor chat (cap4 par. 4.4).
 *
 * Public properties hold ONLY what the user typed or chose: the email card, the dialog state and the solution stay
 * on the server (tutor_sessions) and the view receives the browser-safe projection of the email
 * (`_meta.browser_safe_fields`) until the dialog of that email is closed. Livewire serialises public properties into
 * the page, so nothing from the card may ever be stored in one.
 *
 * When the last email is closed the component emits `tutorCompleted` (Livewire) and `tutor-completed` (browser
 * event), and the root element carries `data-tutor-completed="true"`, so the training page can enable "Next".
 */
class TutorChat extends Component
{
    /** Current classification chosen by the user: 'phishing' | 'legitimate' | ''. */
    public string $choice = '';

    /** Message being written. */
    public string $message = '';

    /** Friendly notice (rate limit, busy, finished email). */
    public ?string $notice = null;

    public bool $completed = false;

    private ?TutorConversation $conversation = null;

    private function conversation(): TutorConversation
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        return $this->conversation ??= new TutorConversation($user);
    }

    public function mount(): void
    {
        $conversation = $this->conversation();
        $this->completed = $conversation->session()->isCompleted();
        // Resume: keep the classification the user had chosen on the current email.
        $this->choice = $conversation->dialog()->classification?->value ?? '';
        if ($this->completed) {
            $this->dispatchBrowserEvent('tutor-completed');
        }
    }

    public function send(): void
    {
        $this->notice = null;
        $this->resetErrorBag();
        $max = (int) config('tutor.max_message_chars', 1000);
        $this->validate(
            ['message' => "max:{$max}", 'choice' => 'nullable|in:phishing,legitimate'],
            ['message.max' => "Please keep your message under {$max} characters."],
        );

        $result = $this->conversation()->submit($this->choice, $this->message);
        if ($result->status === ChatSubmitResult::REJECTED) {
            $this->addError('message', $result->notice);

            return;
        }
        if (! $result->ok()) {
            $this->notice = $result->notice;

            return;
        }
        $this->message = '';
        if (! $this->completed && $this->conversation()->session()->isCompleted()) {
            $this->completed = true;
            $this->emit('tutorCompleted');
            $this->dispatchBrowserEvent('tutor-completed');
        }
    }

    public function nextEmail(): void
    {
        $this->notice = null;
        $this->resetErrorBag();
        if ($this->conversation()->advance()) {
            $this->choice = '';
            $this->message = '';
            // The view moves focus to the new "Exercise N of M" heading (accessibility, keyboard/screen reader).
            $this->dispatchBrowserEvent('tutor-email-changed');
        }
    }

    /** Missed signals shown before "Show all": a long list (up to 14 on one email) buries the main ones. */
    public const MISSED_VISIBLE = 4;

    /**
     * Splits the missed items for the closing view: main signals first (card order kept within each group),
     * the first MISSED_VISIBLE shown, the rest behind a "Show all" disclosure.
     *
     * @param list<array<string, mixed>> $items
     * @return array{shown: list<array<string, mixed>>, more: list<array<string, mixed>>}
     */
    public static function missedPreview(array $items): array
    {
        $main = array_values(array_filter($items, fn (array $i) => ! empty($i['main'])));
        $other = array_values(array_filter($items, fn (array $i) => empty($i['main'])));
        $ordered = array_merge($main, $other);

        return [
            'shown' => array_slice($ordered, 0, self::MISSED_VISIBLE),
            'more' => array_slice($ordered, self::MISSED_VISIBLE),
        ];
    }

    public function render()
    {
        $conversation = $this->conversation();
        $session = $conversation->session();
        $dialog = $conversation->dialog();
        $email = $conversation->browserEmail($dialog);
        $closed = $dialog->phase === Phase::Closed && $dialog->closingView !== null;

        $closing = null;
        if ($closed) {
            // The dialog is closed: the annotated solution of THIS email may now be shown (cap4 par. 4.2.3).
            $view = $dialog->closingView;
            $annotate = fn (array $items) => array_map(fn (array $i) => [
                'label' => $i['label'] ?? '',
                'explanation' => $i['explanation'] ?? '',
                'field' => $i['target']['field'] ?? null,
                'text' => isset($i['target']['text']) ? FirstName::fillText($i['target']['text'], $conversation->displayName()) : null,
                'main' => ! empty($i['main']),
            ], $items);
            $closing = [
                'verdict' => $view->verdict === Classification::Phishing ? 'Phishing' : 'Legitimate',
                'correct' => $dialog->classification === $view->verdict,
                'message' => $view->message,
                'found' => $annotate($view->found),
                'missed' => self::missedPreview($annotate($view->missed)),
            ];
        }

        return view('livewire.tutor-chat', [
            'email' => $email,
            'emailDate' => self::formatDate($email['date'] ?? null),
            'position' => $dialog->position,
            'total' => $conversation->total(),
            'transcript' => TutorConversation::transcript($dialog),
            'awaitingFirst' => $dialog->phase === Phase::AwaitingExplanation,
            'accepting' => in_array($dialog->phase, [Phase::AwaitingExplanation, Phase::AwaitingReply], true),
            'closing' => $closing,
            'isLast' => $dialog->position >= $conversation->total(),
            'completed' => $session->isCompleted(),
            'maxChars' => (int) config('tutor.max_message_chars', 1000),
            'fieldLabels' => self::FIELD_LABELS,
        ]);
    }

    private const FIELD_LABELS = [
        'sender_name' => 'Sender name',
        'sender_address' => 'Sender address',
        'reply_to' => 'Reply-to',
        'recipient' => 'Recipient',
        'date' => 'Date',
        'subject' => 'Subject',
        'body' => 'Body',
        'link' => 'Link',
        'attachment' => 'Attachment',
    ];


    private static function formatDate(?string $date): ?string
    {
        if ($date === null) {
            return null;
        }
        try {
            return Carbon::parse($date)->format('D, j M Y H:i');
        } catch (\Throwable) {
            return $date;
        }
    }
}
