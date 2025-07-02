<?php

namespace LLMSpeak\Google\Repositories;

abstract class GeminiAPIRepository
{
    protected ?string $api_key = null;

    public function withApikey(string $api_key): static
    {
        $this->api_key = $api_key;

        return $this;
    }
}
