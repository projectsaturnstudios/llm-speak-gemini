<?php

namespace LLMSpeak\Gemini\Drivers;

use LLMSpeak\Gemini\GeminiEmbeddingsRequest;
use LLMSpeak\Gemini\GeminiEmbeddingsResponse;
use LLMSpeak\Core\Drivers\LLMEmbeddingsDriver;
use LLMSpeak\Core\Support\Requests\LLMSpeakEmbeddingsRequest;
use LLMSpeak\Core\Support\Responses\LLMSpeakEmbeddingsResponse;

class GeminiLLMSpeakEmbeddingsDriver extends LLMEmbeddingsDriver
{
    public function convertRequest(LLMSpeakEmbeddingsRequest $communique): GeminiEmbeddingsRequest
    {
        // Convert input to Gemini's content structure with parts
        $content = [];
        
        if (is_string($communique->input)) {
            // Single text input
            $content = ['parts' => [['text' => $communique->input]]];
        } elseif (is_array($communique->input)) {
            // Multiple text inputs - create parts array
            $parts = [];
            foreach ($communique->input as $text) {
                $parts[] = ['text' => (string) $text];
            }
            $content = ['parts' => $parts];
        }

        return new GeminiEmbeddingsRequest(
            model: $communique->model,
            content: $content,
            taskType: $communique->task_type,
            title: null, // Not available in universal request
            outputDimensionality: $communique->dimensions
        );
    }

    public function convertResponse(LLMSpeakEmbeddingsResponse $communique): GeminiEmbeddingsResponse
    {
        // Convert universal format to Gemini's embedding structure
        $embedding = null;
        
        if (!empty($communique->data)) {
            // Get the first embedding from the data array
            $firstEmbedding = $communique->getFirstEmbedding();
            if ($firstEmbedding) {
                // Gemini expects embedding with 'values' key
                $embedding = [
                    'values' => $firstEmbedding
                ];
            }
        }

        return new GeminiEmbeddingsResponse(
            embedding: $embedding,
            status_code: 200,
            headers: []
        );
    }

    public function translateRequest(mixed $communique): LLMSpeakEmbeddingsRequest
    {
        if(!$communique instanceof GeminiEmbeddingsRequest) throw new \InvalidArgumentException('Expected GeminiEmbeddingsRequest instance.');
        
        // Convert Gemini content structure back to input
        $input = '';
        if (!empty($communique->content['parts'])) {
            $textParts = [];
            foreach ($communique->content['parts'] as $part) {
                if (isset($part['text'])) {
                    $textParts[] = $part['text'];
                }
            }
            $input = implode(' ', $textParts);
        }

        return new LLMSpeakEmbeddingsRequest(
            model: $communique->model,
            input: $input,
            encoding_format: null, // Not available in Gemini request
            dimensions: $communique->outputDimensionality,
            task_type: $communique->taskType
        );
    }

    public function translateResponse(mixed $communique): LLMSpeakEmbeddingsResponse
    {
        if(!$communique instanceof GeminiEmbeddingsResponse) throw new \InvalidArgumentException('Expected GeminiEmbeddingsResponse instance.');
        
        // Convert Gemini embedding to universal data format
        $data = [];
        if ($communique->hasEmbedding() && $communique->getEmbeddingValues()) {
            $data[] = [
                'object' => 'embedding',
                'embedding' => $communique->getEmbeddingValues(),
                'index' => 0
            ];
        }

        // Gemini doesn't provide usage info for embeddings, so create basic structure
        $usage = [
            'prompt_tokens' => 0,
            'total_tokens' => 0
        ];

        return new LLMSpeakEmbeddingsResponse(
            model: 'text-embedding-004', // Use standard Gemini embedding model name
            data: $data,
            usage: $usage,
            object: 'list',
            metadata: [
                'status_code' => $communique->status_code,
                'embedding_dimensions' => $communique->getDimensions()
            ]
        );
    }
}
