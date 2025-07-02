<?php

namespace LLMSpeak\Google\Repositories;

use LLMSpeak\Google\Support\Facades\Gemini;
use LLMSpeak\Google\Actions\GoogleAPI\GenerateContent\ChatEndpoint;

class GeminiGenerateContentAPIRepository extends GeminiAPIRepository
{
    protected ?string $model = null;
    protected ?array $messages = null;

    protected ?int $max_tokens = null;
    protected ?array $tools = null;
    protected ?array $system_prompt = null;
    protected ?float $temperature = null;

    public function withModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function withChat(array $conversation): ChatEndpoint
    {
        $this->messages = $conversation;
        return new ChatEndpoint(
            url: Gemini::api_url(),
            api_key: $this->api_key,
            model: $this->model,
            contents: $this->messages,
            system_prompt: $this->system_prompt,
            max_tokens: $this->max_tokens,
            tools: $this->tools,
            temperature: $this->temperature
        );

    }

    public function withSystemPrompt(array $prompt): static
    {
        $this->system_prompt = $prompt;
        return $this;
    }

    public function withMaxTokens(int $tokens): static
    {
        $this->max_tokens = $tokens;
        return $this;
    }

    public function withTools(array $tools): static
    {
        $this->tools = $tools;
        return $this;
    }

    public function withTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

}
