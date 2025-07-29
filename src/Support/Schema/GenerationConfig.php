<?php

namespace LLMSpeak\Gemini\Support\Schema;

use Spatie\LaravelData\Data;

class GenerationConfig extends Data
{
    public function __construct(
        public readonly ?float $temperature = null,
        public readonly ?float $topP = null,
        public readonly ?int $topK = null,
        public readonly ?array $stopSequences = null,
        public readonly ?int $maxOutputTokens = null,
        public readonly ?int $candidateCount = null,
        public readonly ?string $responseMimeType = null,
        public readonly ?object $responseSchema = null,
        public readonly ?ThinkingConfig $thinkingConfig = null,
    ) {}

    public function toArray(): array
    {
        return [
            'temperature' => $this->temperature,
            'topP' => $this->topP,
            'topK' => $this->topK,
            'stopSequences' => $this->stopSequences,
            'maxOutputTokens' => $this->maxOutputTokens,
            'candidateCount' => $this->candidateCount,
            'responseMimeType' => $this->responseMimeType,
            'responseSchema' => $this->responseSchema,
            'thinkingConfig' => $this->thinkingConfig?->toArray(),
        ];
    }
}
