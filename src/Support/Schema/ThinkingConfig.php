<?php

namespace LLMSpeak\Gemini\Support\Schema;

use Spatie\LaravelData\Data;

class ThinkingConfig extends Data
{
    public function __construct(
        public readonly ?int $thinkingBudget = null,
        public readonly ?bool $includeThoughts = null,
    ) {}

    public function toArray(): array
    {
        return [
            'thinkingBudget' => $this->thinkingBudget,
            'includeThoughts' => $this->includeThoughts,
        ];
    }
}
