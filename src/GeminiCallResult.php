<?php

namespace LLMSpeak\Google;

use Spatie\LaravelData\Data;

class GeminiCallResult extends Data
{
    public function __construct(
        public readonly ?array $candidates = null,
        public readonly ?array $usageMetadata = null,
        public readonly ?string $modelVersion = null,
        public readonly ?string $responseId = null,
        public readonly ?array $promptFeedback = null,
    )
    {}
}
