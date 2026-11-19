<?php

namespace App\Services\Tutor;

final class ClosingView
{
    /**
     * @param list<array<string, mixed>> $found card items recognized during the dialog
     * @param list<array<string, mixed>> $missed card items never recognized
     */
    public function __construct(
        /** Taken from the card, never written by the model (G2). */
        public readonly Classification $verdict,
        public readonly array $found,
        public readonly array $missed,
        public readonly string $message,
        public readonly string $source, // 'model' | 'fallback'
    ) {
    }

    /** Ids only: the card items are reloaded from the card on restore. */
    public function toArray(): array
    {
        $ids = fn (array $items) => array_map(fn (array $i) => $i['id'], $items);

        return ['verdict' => $this->verdict->value, 'found' => $ids($this->found), 'missed' => $ids($this->missed), 'message' => $this->message, 'source' => $this->source];
    }

    public static function fromArray(array $a, array $card): self
    {
        $byId = [];
        foreach (Materials::cardItems($card) as $item) {
            $byId[$item['id']] = $item;
        }
        $items = fn (array $ids) => array_map(fn (string $id) => $byId[$id], $ids);

        return new self(Classification::from($a['verdict']), $items($a['found']), $items($a['missed']), $a['message'], $a['source']);
    }
}
