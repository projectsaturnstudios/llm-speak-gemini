<?php

namespace LLMSpeak\Google;

use Spatie\LaravelData\Data;

class GeminiEmbeddingResult extends Data
{
    public function __construct(
        public readonly ?array $embedding
    )
    {}
}
