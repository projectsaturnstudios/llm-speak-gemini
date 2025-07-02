<?php

namespace LLMSpeak\Google\Builders;


class SystemPromptBuilder
{
    protected array $conversation = [];

    public function addText(string $content): static
    {
        $this->conversation[] = [
            'text' => $content,
        ];

        return $this;
    }

    public function addContent(array $content): static
    {
        return $this;
    }

    public function render(): array
    {
        return ['parts' => $this->conversation];
    }
}
