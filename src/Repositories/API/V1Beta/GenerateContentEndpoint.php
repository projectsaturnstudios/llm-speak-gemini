<?php

namespace LLMSpeak\Gemini\Repositories\API\V1Beta;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LLMSpeak\Gemini\Support\Schema\GenerationConfig;

class GenerateContentEndpoint
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Handle Google Gemini Generate Content API request
     *
     * @param string $url - Full API URL (e.g., "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent")
     * @param string $apiKey - Google API key
     * @param array $contents - Conversation contents array (required)
     * @param GenerationConfig|null $generationConfig - Generation configuration (temperature, maxOutputTokens, etc.)
     * @param array|null $tools - Tool definitions array
     * @param array|null $toolConfig - Tool configuration for function calling modes
     * @param array|null $systemInstruction - System instruction to guide model behavior
     * @param array|null $safetySettings - Safety filtering configuration
     * @param string|null $cachedContent - Reference to cached content for performance
     * @return array - Contains 'headers', 'status_code', 'body', 'raw_body' keys
     * @throws GuzzleException
     */
    public function handle(
        string $url,
        string $apiKey,
        array $contents,
        ?GenerationConfig $generationConfig = null,
        ?array $tools = null,
        ?array $toolConfig = null,
        ?array $systemInstruction = null,
        ?array $safetySettings = null,
        ?string $cachedContent = null
    ): array {
        // Build request payload
        $payload = [
            'contents' => $contents,
        ];

        // Add optional parameters only if provided
        if ($generationConfig !== null) {
            $payload['generationConfig'] = $generationConfig->toArray();
        }

        if ($tools !== null) {
            $payload['tools'] = $tools;
        }

        if ($toolConfig !== null) {
            $payload['toolConfig'] = $toolConfig;
        }

        if ($systemInstruction !== null) {
            $payload['system_instruction'] = $systemInstruction;
        }

        if ($safetySettings !== null) {
            $payload['safetySettings'] = $safetySettings;
        }

        if ($cachedContent !== null) {
            $payload['cachedContent'] = $cachedContent;
        }

        // Make the request
        $response = $this->client->post($url, [
            'headers' => [
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        // Get response body
        $body = $response->getBody()->getContents();

        // Return complete response data
        return [
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => json_decode($body, true),
            'raw_body' => $body,
        ];
    }
}
