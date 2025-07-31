<?php

namespace LLMSpeak\Gemini;

use LLMSpeak\Gemini\Support\Schema\GenerationConfig;
use LLMSpeak\Gemini\Support\Schema\ThinkingConfig;
use LLMSpeak\Gemini\Repositories\API\V1Beta\GenerateContentEndpoint;
use Spatie\LaravelData\Data;

/**
 * GeminiGenerateRequest - Google Gemini API Request Builder
 *
 * Usage Examples:
 *
 * // Traditional setters
 * $request = new GeminiGenerateRequest('gemini-2.5-flash', $contents)
 *     ->setTemperature(0.8)
 *     ->setMaxOutputTokens(1000)
 *     ->setTopP(0.9);
 *
 * // Generic set() method
 * $request = $request->set('temperature', 0.8)->set('maxOutputTokens', 1000);
 *
 * // Batch setting
 * $request = $request->setMultiple([
 *     'temperature' => 0.8,
 *     'maxOutputTokens' => 1000,
 *     'topP' => 0.9,
 *     'topK' => 40
 * ]);
 *
 * // Magic methods (camelCase gets converted to snake_case)
 * $request = $request->setTemperature(0.8)->setMaxOutputTokens(1000)->setTopK(40);
 *
 * // Convert to GenerateContentEndpoint parameters
 * $params = $request->toArray();
 * $response = (new GenerateContentEndpoint())->handle(...$params);
 *
 * // Thinking configuration
 * $request = $request->setThinkingBudget(2048)->setIncludeThoughts(true);
 *
 * // Response formatting
 * $request = $request->setResponseMimeType('application/json')->setResponseSchema($schema);
 *
 * // Direct API call with fluent response
 * $response = $request->post(); // Returns GeminiGenerateResponse object
 */
class GeminiGenerateRequest extends Data
{
    protected string $url;
    protected string $api_key;

    public function __construct(
        public readonly string $model,
        public readonly array $contents,

        // GenerationConfig parameters (flattened for convenience)
        public readonly ?float $temperature = null,
        public readonly ?float $topP = null,
        public readonly ?int $topK = null,
        public readonly ?array $stopSequences = null,
        public readonly ?int $maxOutputTokens = null,
        public readonly ?int $candidateCount = null,
        public readonly ?string $responseMimeType = null,
        public readonly ?object $responseSchema = null,

        // ThinkingConfig parameters
        public readonly ?int $thinkingBudget = null,
        public readonly ?bool $includeThoughts = null,

        // Main API parameters
        public readonly ?GenerationConfig $generationConfig = null,
        public readonly ?array $tools = null,
        public readonly ?array $toolConfig = null,
        public readonly ?array $systemInstruction = null,
        public readonly ?array $safetySettings = null,
        public readonly ?string $cachedContent = null
    )
    {
        $this->api_key = env('GEMINI_API_KEY');
        $this->url = config('llms.chat-providers.drivers.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
    }

    /**
     * Generic method to set any property and return a new instance
     */
    public function set(string $property, mixed $value): self
    {
        $currentData = [
            'model' => $this->model,
            'contents' => $this->contents,
            'temperature' => $this->temperature,
            'topP' => $this->topP,
            'topK' => $this->topK,
            'stopSequences' => $this->stopSequences,
            'maxOutputTokens' => $this->maxOutputTokens,
            'candidateCount' => $this->candidateCount,
            'responseMimeType' => $this->responseMimeType,
            'responseSchema' => $this->responseSchema,
            'thinkingBudget' => $this->thinkingBudget,
            'includeThoughts' => $this->includeThoughts,
            'generationConfig' => $this->generationConfig,
            'tools' => $this->tools,
            'toolConfig' => $this->toolConfig,
            'systemInstruction' => $this->systemInstruction,
            'safetySettings' => $this->safetySettings,
            'cachedContent' => $this->cachedContent,
        ];

        $currentData[$property] = $value;

        return new self(...$currentData);
    }

    /**
     * Batch setter - set multiple properties at once
     */
    public function setMultiple(array $properties): self
    {
        $instance = $this;
        foreach ($properties as $property => $value) {
            $instance = $instance->set($property, $value);
        }
        return $instance;
    }

    /**
     * Magic method approach - could replace all setter methods
     * Usage: $request->setTemperature(0.8) or $request->setTopK(40)
     */
    public function __call(string $method, array $arguments): self
    {
        if (str_starts_with($method, 'set')) {
            $property = lcfirst(substr($method, 3)); // Remove 'set' and lowercase first char
            return $this->set($property, $arguments[0] ?? null);
        }

        throw new \BadMethodCallException("Method {$method} does not exist");
    }

    // GenerationConfig Parameter Setters

    public function setTemperature(float $temperature): self
    {
        return $this->set('temperature', $temperature);
    }

    public function setTopP(float $topP): self
    {
        return $this->set('topP', $topP);
    }

    public function setTopK(int $topK): self
    {
        return $this->set('topK', $topK);
    }

    public function setStopSequences(array $stopSequences): self
    {
        return $this->set('stopSequences', $stopSequences);
    }

    public function setMaxOutputTokens(int $maxOutputTokens): self
    {
        return $this->set('maxOutputTokens', $maxOutputTokens);
    }

    public function setCandidateCount(int $candidateCount): self
    {
        return $this->set('candidateCount', $candidateCount);
    }

    public function setResponseMimeType(string $responseMimeType): self
    {
        return $this->set('responseMimeType', $responseMimeType);
    }

    public function setResponseSchema(object $responseSchema): self
    {
        return $this->set('responseSchema', $responseSchema);
    }

    // ThinkingConfig Parameter Setters

    public function setThinkingBudget(int $thinkingBudget): self
    {
        return $this->set('thinkingBudget', $thinkingBudget);
    }

    public function setIncludeThoughts(bool $includeThoughts): self
    {
        return $this->set('includeThoughts', $includeThoughts);
    }

    // Main API Parameter Setters

    public function setGenerationConfig(GenerationConfig $generationConfig): self
    {
        return $this->set('generationConfig', $generationConfig);
    }

    public function setTools(array $tools): self
    {
        return $this->set('tools', $tools);
    }

    public function setToolConfig(array $toolConfig): self
    {
        return $this->set('toolConfig', $toolConfig);
    }

    public function setSystemInstruction(array $systemInstruction): self
    {
        return $this->set('systemInstruction', $systemInstruction);
    }

    public function setSafetySettings(array $safetySettings): self
    {
        return $this->set('safetySettings', $safetySettings);
    }

    public function setCachedContent(string $cachedContent): self
    {
        return $this->set('cachedContent', $cachedContent);
    }

    // Convenience Methods for Complex Configurations

    /**
     * Set system instruction from a simple text string
     */
    public function setSystemPrompt(string $text): self
    {
        return $this->setSystemInstruction([
            'parts' => [
                ['text' => $text]
            ]
        ]);
    }

    /**
     * Set function calling mode (AUTO, NONE, ANY)
     */
    public function setFunctionCallingMode(string $mode): self
    {
        return $this->setToolConfig([
            'functionCallingConfig' => [
                'mode' => $mode
            ]
        ]);
    }

    /**
     * Configure thinking with both budget and thoughts inclusion
     */
    public function setThinkingConfig(int $budget, bool $includeThoughts = false): self
    {
        return $this->setThinkingBudget($budget)->setIncludeThoughts($includeThoughts);
    }

    /**
     * Set JSON response format with optional schema
     */
    public function setJsonResponse(?object $schema = null): self
    {
        $instance = $this->setResponseMimeType('application/json');
        if ($schema) {
            $instance = $instance->setResponseSchema($schema);
        }
        return $instance;
    }

    /**
     * Add a single tool function declaration
     */
    public function addTool(array $functionDeclaration): self
    {
        $currentTools = $this->tools ?? [];

        // Ensure tools array has the correct structure
        if (empty($currentTools)) {
            $currentTools = [
                ['functionDeclarations' => []]
            ];
        }

        $currentTools[0]['functionDeclarations'][] = $functionDeclaration;

        return $this->setTools($currentTools);
    }

    /**
     * Set safety settings with common configuration
     */
    public function setSafetyLevel(string $level = 'BLOCK_MEDIUM_AND_ABOVE'): self
    {
        return $this->setSafetySettings([
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => $level
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => $level
            ],
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => $level
            ],
            [
                'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                'threshold' => $level
            ]
        ]);
    }

    // Utility Methods

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    /**
     * Build the full API URL for the model
     */
    public function buildApiUrl(): string
    {
        $baseUrl = rtrim($this->url, '/');
        return "{$baseUrl}/models/{$this->model}:generateContent";
    }

    /**
     * Convert to GenerateContentEndpoint parameters
     */
    public function toArray(): array
    {
        // Build the generation config from individual parameters if no config object provided
        $generationConfig = $this->generationConfig;

        if (!$generationConfig && $this->hasGenerationConfigParams()) {
            $thinkingConfig = null;
            if ($this->thinkingBudget !== null || $this->includeThoughts !== null) {
                $thinkingConfig = new ThinkingConfig(
                    thinkingBudget: $this->thinkingBudget,
                    includeThoughts: $this->includeThoughts
                );
            }

            $generationConfig = new GenerationConfig(
                temperature: $this->temperature,
                topP: $this->topP,
                topK: $this->topK,
                stopSequences: $this->stopSequences,
                maxOutputTokens: $this->maxOutputTokens,
                candidateCount: $this->candidateCount,
                responseMimeType: $this->responseMimeType,
                responseSchema: $this->responseSchema,
                thinkingConfig: $thinkingConfig
            );
        }

        return [
            'url' => $this->buildApiUrl(),
            'apiKey' => $this->api_key,
            'contents' => $this->contents,
            'generationConfig' => $generationConfig,
            'tools' => $this->tools,
            'toolConfig' => $this->toolConfig,
            'systemInstruction' => $this->systemInstruction,
            'safetySettings' => $this->safetySettings,
            'cachedContent' => $this->cachedContent,
        ];
    }

    /**
     * Get a clean array for debugging
     */
    public function toDebugArray(): array
    {
        return [
            'model' => $this->model,
            'url' => $this->buildApiUrl(),
            'contents_count' => count($this->contents),
            'temperature' => $this->temperature,
            'maxOutputTokens' => $this->maxOutputTokens,
            'topP' => $this->topP,
            'topK' => $this->topK,
            'candidateCount' => $this->candidateCount,
            'responseMimeType' => $this->responseMimeType,
            'thinkingBudget' => $this->thinkingBudget,
            'includeThoughts' => $this->includeThoughts,
            'has_tools' => !empty($this->tools),
            'has_system_instruction' => !empty($this->systemInstruction),
            'has_safety_settings' => !empty($this->safetySettings),
            'has_cached_content' => !empty($this->cachedContent),
            'tool_count' => $this->getToolCount(),
        ];
    }

    // Helper Methods

    /**
     * Check if any generation config parameters are set
     */
    private function hasGenerationConfigParams(): bool
    {
        return $this->temperature !== null ||
               $this->topP !== null ||
               $this->topK !== null ||
               $this->stopSequences !== null ||
               $this->maxOutputTokens !== null ||
               $this->candidateCount !== null ||
               $this->responseMimeType !== null ||
               $this->responseSchema !== null ||
               $this->thinkingBudget !== null ||
               $this->includeThoughts !== null;
    }

    /**
     * Get total number of tool functions
     */
    private function getToolCount(): int
    {
        if (!$this->tools) return 0;

        $count = 0;
        foreach ($this->tools as $tool) {
            if (isset($tool['functionDeclarations'])) {
                $count += count($tool['functionDeclarations']);
            }
        }
        return $count;
    }

    /**
     * Validate the request configuration
     */
    public function validateRequest(): array
    {
        $errors = [];

        // Validate model
        if (empty($this->model)) {
            $errors[] = 'Model is required';
        }

        // Validate contents
        if (empty($this->contents)) {
            $errors[] = 'Contents array is required';
        }

        // Validate API key
        if (empty($this->api_key)) {
            $errors[] = 'GEMINI_API_KEY environment variable is required';
        }

        // Validate temperature range
        if ($this->temperature !== null && ($this->temperature < 0 || $this->temperature > 2)) {
            $errors[] = 'Temperature must be between 0 and 2';
        }

        // Validate topP range
        if ($this->topP !== null && ($this->topP < 0 || $this->topP > 1)) {
            $errors[] = 'TopP must be between 0 and 1';
        }

        // Validate topK
        if ($this->topK !== null && $this->topK < 1) {
            $errors[] = 'TopK must be >= 1';
        }

        // Validate maxOutputTokens
        if ($this->maxOutputTokens !== null && $this->maxOutputTokens < 1) {
            $errors[] = 'MaxOutputTokens must be >= 1';
        }

        // Validate candidateCount
        if ($this->candidateCount !== null && ($this->candidateCount < 1 || $this->candidateCount > 8)) {
            $errors[] = 'CandidateCount must be between 1 and 8';
        }

        return $errors;
    }

    /**
     * Check if request is valid
     */
    public function isValid(): bool
    {
        return empty($this->validateRequest());
    }

    /**
     * Execute the API request and return a structured response object
     *
     * This method completes the fluent API chain by:
     * 1. Converting the request to endpoint parameters
     * 2. Calling the GenerateContentEndpoint
     * 3. Wrapping the response in a GeminiGenerateResponse object
     *
     * Usage:
     * $response = (new GeminiGenerateRequest('gemini-2.5-flash', $contents))
     *     ->setTemperature(0.8)
     *     ->setMaxOutputTokens(1000)
     *     ->post();
     *
     * $text = $response->getTextContent();
     * $tokens = $response->getTotalTokens();
     */
    public function post(): GeminiGenerateResponse
    {
        // Get endpoint parameters from this request
        $params = $this->toArray();

        // Create endpoint instance and make the API call
        $endpoint = new GenerateContentEndpoint();
        $apiResponse = $endpoint->handle(...$params);

        // Create structured response object from API response
        return GeminiGenerateResponse::fromApiResponse(
            response: $apiResponse['body'] ?? [],
            headers: $apiResponse['headers'] ?? [],
            statusCode: $apiResponse['status_code'] ?? 500,
            rawBody: $apiResponse['raw_body'] ?? null
        );
    }
}
