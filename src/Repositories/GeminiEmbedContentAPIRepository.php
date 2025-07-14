<?php

namespace LLMSpeak\Google\Repositories;

use LLMSpeak\Google\Actions\GoogleAPI\EmbedContent\EmbedEndpoint;
use LLMSpeak\Google\Enums\GeminiTaskType;
use LLMSpeak\Google\Support\Facades\Gemini;

class GeminiEmbedContentAPIRepository extends GeminiAPIRepository
{
    protected ?string $model = null;
    protected ?array $content = null;
    protected ?GeminiTaskType $taskType = null;

    public function withModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function asTaskType(GeminiTaskType $taskType): static
    {
        $this->taskType = $taskType;

        return $this;
    }

    public function withContent(array $conversation): EmbedEndpoint
    {
        $this->content = $conversation;
        return new EmbedEndpoint(
            url: Gemini::api_url(),
            api_key: $this->api_key,
            model: $this->model,
            content: $this->content,
            task_type: $this->taskType
        );
    }

}
