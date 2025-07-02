<?php

namespace LLMSpeak\Google\Builders;

use LLMSpeak\Google\Enums\GeminiRole;

class ConversationBuilder
{
    protected array $conversation = [];

    public function addText(GeminiRole $role, string $content): static
    {
        $this->conversation[] = [
            'role' => $role->value,
            'parts' => [['text' => $content]],
        ];
        return $this;
    }

    public function addToolRequest(string $name, array $input): static
    {
        $this->conversation[] = [
            'role' => GeminiRole::MODEL->value,
            'parts' => [
                ['functionCall' => [
                    'name' => $name,
                    'args' => $input,
                ]]
            ],
        ];
        return $this;
    }

    public function addToolResult(string $name, mixed $content): static
    {
        $this->conversation[] = [
            'role' => GeminiRole::FUNCTION->value,
            'parts' => [
                ['functionResponse' => [
                    'name' => $name,
                    'response' => [
                        'name' => $name,
                        'content' => $content
                    ],
                ]]
            ],
        ];
        return $this;
    }

    public function addContent(GeminiRole $role, array $content): static
    {
        return $this;
    }

    public function render(): array
    {
        return $this->conversation;
    }
}
