<?php

namespace LLMSpeak\Gemini\Drivers;

use LLMSpeak\Core\Drivers\LLMTranslationDriver;
use LLMSpeak\Core\Support\Requests\LLMSpeakChatRequest;
use LLMSpeak\Core\Support\Responses\LLMSpeakChatResponse;
use LLMSpeak\Gemini\GeminiGenerateRequest;
use LLMSpeak\Gemini\GeminiGenerateResponse;

class GeminiLLMSpeakTranslationDriver extends LLMTranslationDriver
{
    public function convertRequest(LLMSpeakChatRequest $communique): GeminiGenerateRequest
    {
        // Convert Conversation to Gemini contents format
        $contents = [];
        if ($communique->messages) {
            foreach ($communique->messages->getEntries() as $message) {
                $role = strtolower($message->role->value);
                // Convert MODEL role to 'model' for Gemini
                if ($role === 'assistant') {
                    $role = 'model';
                }
                
                $contents[] = [
                    'role' => $role,
                    'parts' => [
                        ['text' => $message->content]
                    ]
                ];
            }
        }

        // Convert SystemInstructions to systemInstruction format
        $systemInstruction = null;
        if ($communique->system_instructions) {
            $systemEntries = $communique->system_instructions->getEntries();
            if ($systemEntries->isNotEmpty()) {
                $systemText = $systemEntries->map(function ($instruction) {
                    return $instruction->content;
                })->implode("\n\n");

                // Gemini expects systemInstruction as array with parts
                $systemInstruction = [
                    'parts' => [
                        ['text' => $systemText]
                    ]
                ];
            }
        }

        // Convert ToolKit to Gemini tools format
        $tools = null;
        if ($communique->tools) {
            $functionDeclarations = [];
            foreach ($communique->tools->getTools() as $tool) {
                $functionDeclarations[] = [
                    'name' => $tool->tool,
                    'description' => $tool->description,
                    'parameters' => $tool->inputSchema
                ];
            }
            
            if (!empty($functionDeclarations)) {
                $tools = [
                    ['functionDeclarations' => $functionDeclarations]
                ];
            }
        }

        return new GeminiGenerateRequest(
            model: $communique->model,
            contents: $contents,
            temperature: $communique->temperature,
            topP: $communique->top_p,
            topK: $communique->top_k,
            stopSequences: $communique->stop,
            maxOutputTokens: $communique->max_tokens,
            tools: $tools,
            systemInstruction: $systemInstruction,
            // Map reasoning to thinking config
            thinkingBudget: $communique->reasoning['budget'] ?? null,
            includeThoughts: $communique->reasoning['include_thoughts'] ?? null,
            // Map response format
            responseMimeType: $communique->response_format?->type ?? null,
            responseSchema: $communique->response_format?->schema ?? null
        );
    }

    public function convertResponse(LLMSpeakChatResponse $communique): GeminiGenerateResponse
    {
        // Convert LLMSpeak universal format to Gemini's format
        $candidates = [];

        // Convert choices to Gemini candidates format
        foreach ($communique->choices as $choice) {
            $content = $choice['message']['content'] ?? $choice['content'] ?? '';
            $finishReason = $choice['finish_reason'] ?? $communique->getFinishReason();

            $candidates[] = [
                'content' => [
                    'parts' => [
                        [
                            'text' => $content
                        ]
                    ],
                    'role' => 'model'
                ],
                'finishReason' => $this->mapToGeminiFinishReason($finishReason),
                'index' => $choice['index'] ?? 0,
                'safetyRatings' => []
            ];
        }

        // Convert usage to Gemini's usageMetadata format
        $usageMetadata = [
            'promptTokenCount' => $communique->getPromptTokens() ?? 0,
            'candidatesTokenCount' => $communique->getCompletionTokens() ?? 0,
            'totalTokenCount' => $communique->getTotalTokens() ?? 0
        ];

        return new GeminiGenerateResponse(
            candidates: $candidates,
            usageMetadata: $usageMetadata,
            modelVersion: $communique->model,
            promptFeedback: null,
            headers: [],
            statusCode: 200,
            rawBody: null
        );
    }

    public function translateRequest(mixed $communique): LLMSpeakChatRequest
    {
        if(!$communique instanceof GeminiGenerateRequest) throw new \InvalidArgumentException('Expected GeminiGenerateRequest instance.');
        
        // Convert Gemini contents back to Conversation
        $conversation = null;
        if (!empty($communique->contents)) {
            $chatMessages = [];
            
            foreach ($communique->contents as $content) {
                // Extract role and parts from Gemini format
                $role = $content['role'] ?? 'user';
                $parts = $content['parts'] ?? [];
                
                // Combine text parts
                $textContent = '';
                foreach ($parts as $part) {
                    if (isset($part['text'])) {
                        $textContent .= $part['text'];
                    }
                }
                
                if ($textContent) {
                    $chatRole = $role === 'model' ? \LLMSpeak\Core\Enums\ChatRole::MODEL : \LLMSpeak\Core\Enums\ChatRole::USER;
                    $chatMessages[] = new \LLMSpeak\Core\Support\Schema\Conversation\ChatMessage($chatRole, $textContent);
                }
            }
            
            if (!empty($chatMessages)) {
                $conversation = new \LLMSpeak\Core\Support\Schema\Conversation\Conversation($chatMessages);
            }
        }

        // Convert Gemini systemInstruction back to SystemInstructions
        $systemInstructions = null;
        if ($communique->systemInstruction) {
            $systemText = '';
            if (isset($communique->systemInstruction['parts'])) {
                foreach ($communique->systemInstruction['parts'] as $part) {
                    if (isset($part['text'])) {
                        $systemText .= $part['text'];
                    }
                }
            }
            
            if ($systemText) {
                $systemInstruction = new \LLMSpeak\Core\Support\Schema\SystemInstructions\SystemInstruction($systemText);
                $systemInstructions = new \LLMSpeak\Core\Support\Schema\SystemInstructions\SystemInstructions([$systemInstruction]);
            }
        }

        // Convert tools back to ToolKit
        $tools = null;
        if ($communique->tools) {
            $toolDefinitions = [];
            foreach ($communique->tools as $tool) {
                $toolDefinitions[] = new \LLMSpeak\Core\Support\Schema\Tools\ToolDefinition(
                    $tool['function_declarations'][0]['name'] ?? $tool['name'] ?? '',
                    $tool['function_declarations'][0]['description'] ?? $tool['description'] ?? '',
                    $tool['function_declarations'][0]['parameters'] ?? $tool['input_schema'] ?? []
                );
            }
            $tools = new \LLMSpeak\Core\Support\Schema\Tools\ToolKit($toolDefinitions);
        }

        // Convert thinking config back to reasoning
        $reasoning = null;
        if ($communique->thinkingBudget || $communique->includeThoughts) {
            $reasoning = [
                'budget' => $communique->thinkingBudget,
                'include_thoughts' => $communique->includeThoughts
            ];
        }

        // Convert response format back
        $responseFormat = null;
        if ($communique->responseMimeType || $communique->responseSchema) {
            $responseFormat = (object) [
                'type' => $communique->responseMimeType,
                'schema' => $communique->responseSchema
            ];
        }

        return new \LLMSpeak\Core\Support\Requests\LLMSpeakChatRequest(
            model: $communique->model,
            messages: $conversation,
            tools: $tools,
            system_instructions: $systemInstructions,
            max_tokens: $communique->maxOutputTokens,
            temperature: $communique->temperature,
            tool_choice: null, // Gemini doesn't have direct tool_choice equivalent
            response_format: $responseFormat,
            stream: false, // Not directly available in Gemini request
            parallel_function_calling: null, // Not directly available in Gemini request
            top_p: $communique->topP,
            top_k: $communique->topK,
            frequency_penalty: null, // Not supported by Gemini
            presence_penalty: null, // Not supported by Gemini
            stop: $communique->stopSequences,
            reasoning: $reasoning
        );
    }

    public function translateResponse(mixed $communique): LLMSpeakChatResponse
    {
        if(!$communique instanceof GeminiGenerateResponse) throw new \InvalidArgumentException('Expected GeminiGenerateResponse instance.');
        
        // Map Gemini response to universal format
        $choices = [];
        if (!empty($communique->candidates)) {
            foreach ($communique->candidates as $index => $candidate) {
                $messageContent = '';
                if (isset($candidate['content']['parts'])) {
                    foreach ($candidate['content']['parts'] as $part) {
                        if (isset($part['text'])) {
                            $messageContent .= $part['text'];
                        }
                    }
                }
                
                $choices[] = [
                    'index' => $index,
                    'message' => [
                        'role' => $candidate['content']['role'] ?? 'assistant',
                        'content' => $messageContent
                    ],
                    'finish_reason' => $this->mapFromGeminiFinishReason($candidate['finishReason'] ?? null)
                ];
            }
        }

        // Map usage information  
        $usage = [
            'prompt_tokens' => $communique->usageMetadata['promptTokenCount'] ?? 0,
            'completion_tokens' => $communique->usageMetadata['candidatesTokenCount'] ?? 0,
            'total_tokens' => $communique->usageMetadata['totalTokenCount'] ?? 0
        ];

        return new \LLMSpeak\Core\Support\Responses\LLMSpeakChatResponse(
            id: 'gemini_' . uniqid(), // Gemini doesn't provide an ID, so generate one
            model: $communique->model ?? 'gemini-model',
            created: time(), // Gemini doesn't provide created timestamp
            choices: $choices,
            usage: $usage,
            finish_reason: $choices[0]['finish_reason'] ?? null,
            object: 'chat.completion',
            system_fingerprint: null,
            metadata: [
                'model_version' => $communique->modelVersion ?? null,
                'safety_ratings' => $communique->candidates[0]['safetyRatings'] ?? null
            ]
        );
    }

    private function mapFromGeminiFinishReason(?string $finishReason): ?string
    {
        return match ($finishReason) {
            'STOP' => 'stop',
            'MAX_TOKENS' => 'length',
            'SAFETY' => 'content_filter',
            'RECITATION' => 'content_filter',
            'LANGUAGE' => 'content_filter',
            'OTHER' => 'stop',
            default => $finishReason
        };
    }

    /**
     * Map universal finish_reason to Gemini's finish reason
     */
    private function mapToGeminiFinishReason(?string $finishReason): string
    {
        return match($finishReason) {
            'stop' => 'STOP',
            'length' => 'MAX_TOKENS',
            'tool_calls' => 'FUNCTION_CALL',
            'content_filter' => 'SAFETY',
            default => 'STOP'
        };
    }
}
