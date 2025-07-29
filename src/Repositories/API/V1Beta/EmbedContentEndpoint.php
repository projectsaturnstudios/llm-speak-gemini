<?php

namespace LLMSpeak\Gemini\Repositories\API\V1Beta;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class EmbedContentEndpoint
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Handle Google Gemini Embed Content API request
     * 
     * @param string $url - Full API URL (e.g., "https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent")
     * @param string $apiKey - Google API key
     * @param array $content - Required. The content to embed. Only the parts.text fields will be counted.
     * @param string|null $taskType - Optional. Task type for which the embeddings will be used. Not supported on earlier models (models/embedding-001).
     * @param string|null $title - Optional. An optional title for the text. Only applicable when TaskType is RETRIEVAL_DOCUMENT.
     * @param int|null $outputDimensionality - Optional. Reduced dimension for the output embedding. Supported by newer models since 2024 only.
     * 
     * @return array - Complete response with status_code, headers, and body
     * @throws GuzzleException
     */
    public function handle(
        string $url,
        string $apiKey,
        array $content,
        ?string $taskType = null,
        ?string $title = null,
        ?int $outputDimensionality = null
    ): array {
        // Build the payload - extract model from URL for payload
        $modelName = $this->extractModelFromUrl($url);
        $payload = [
            'model' => $modelName, // Required model parameter
            'content' => $content, // Required content parameter
        ];

        // Add optional parameters
        if ($taskType !== null) {
            $payload['taskType'] = $taskType;
        }

        if ($title !== null) {
            $payload['title'] = $title;
        }

        if ($outputDimensionality !== null) {
            $payload['outputDimensionality'] = $outputDimensionality;
        }

        // Make the request
        $response = $this->client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'key' => $apiKey,  // Google uses query parameter for API key
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
        ];
    }

    /**
     * Extract model name from URL for payload
     * URL format: https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent
     * Returns: models/gemini-embedding-001
     */
    private function extractModelFromUrl(string $url): string
    {
        // Extract the model path from URL
        if (preg_match('/\/models\/([^:]+)/', $url, $matches)) {
            return 'models/' . $matches[1];
        }
        
        // Fallback if pattern doesn't match
        return 'models/gemini-embedding-001';
    }
} 