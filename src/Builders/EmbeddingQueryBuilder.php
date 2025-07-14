<?php

namespace LLMSpeak\Google\Builders;


class EmbeddingQueryBuilder
{
    protected array $conversation = [
        'parts' => []
    ];

    public function addQuery(string $text): static
    {
        $this->conversation['parts'][] = ['text' => $text];

        return $this;
    }

    public function render(): array|string
    {
        return $this->conversation;
    }
}
