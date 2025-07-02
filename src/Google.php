<?php

namespace LLMSpeak\Google;

use LLMSpeak\Google\Repositories\GeminiGenerateContentAPIRepository;

class Google
{
    public function __construct(protected array $config)
    {

    }

    public function generateContent(): GeminiGenerateContentAPIRepository
    {
        return new GeminiGenerateContentAPIRepository;
    }

    public function api_url(): string
    {
        return $this->config['api_url'];
    }

    public function api_key(): string
    {
        return $this->config['api_key'];
    }

    public static function boot(): void
    {
        app()->singleton('gemini', function () {
            $results = new static(config('llms.services.google'));

            return $results;
        });
    }
}
