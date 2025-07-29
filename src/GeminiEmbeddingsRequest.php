<?php

namespace LLMSpeak\Gemini;

use Spatie\LaravelData\Data;
use LLMSpeak\Gemini\Repositories\API\V1Beta\EmbedContentEndpoint;
use GuzzleHttp\Exception\GuzzleException;

/**
 * GeminiEmbeddingsRequest - Google Gemini Embeddings API Request Builder
 *
 * Usage Examples:
 *
 * // Traditional setters
 * $request = new GeminiEmbeddingsRequest('gemini-embedding-001', ['parts' => [['text' => 'Generate embeddings for this text']]])
 *     ->setTaskType('RETRIEVAL_DOCUMENT')
 *     ->setTitle('Document Title')
 *     ->setOutputDimensionality(256);
 *
 * // Generic set() method
 * $request = $request->set('taskType', 'SEMANTIC_SIMILARITY')->set('outputDimensionality', 512);
 *
 * // Batch setting
 * $request = $request->setMultiple([
 *     'taskType' => 'CLUSTERING',
 *     'outputDimensionality' => 1024,
 *     'title' => 'Research Paper Abstract'
 * ]);
 *
 * // Magic methods (camelCase gets converted properly)
 * $request = $request->setTaskType('CLASSIFICATION')->setOutputDimensionality(256);
 *
 * // Convert to EmbedContentEndpoint parameters
 * $params = $request->toArray();
 * $response = (new EmbedContentEndpoint())->handle(...$params);
 *
 * // Or make direct API call
 * $response = $request->post(); // Returns GeminiEmbeddingsResponse
 *
 * // Content structure helpers
 * $request = GeminiEmbeddingsRequest::withText('gemini-embedding-001', 'Simple text to embed');
 * $request = GeminiEmbeddingsRequest::withParts('gemini-embedding-001', [
 *     ['text' => 'First part'],
 *     ['text' => 'Second part']
 * ]);
 */
class GeminiEmbeddingsRequest extends Data
{
    protected string $url;
    protected string $api_key;

    public function __construct(
        public readonly string $model,
        public readonly array $content,
        public readonly ?string $taskType = null,
        public readonly ?string $title = null,
        public readonly ?int $outputDimensionality = null
    )
    {
        $this->api_key = env('GEMINI_API_KEY');
        $baseUrl = config('llms.providers.drivers.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $this->url = rtrim($baseUrl, '/') . '/models/' . $this->model . ':embedContent';
    }

    /**
     * Create request with simple text content
     */
    public static function withText(string $model, string $text): self
    {
        return new self($model, ['parts' => [['text' => $text]]]);
    }

    /**
     * Create request with structured parts array
     */
    public static function withParts(string $model, array $parts): self
    {
        return new self($model, ['parts' => $parts]);
    }

    /**
     * Generic method to set any property and return a new instance
     */
    public function set(string $property, mixed $value): self
    {
        $currentData = [
            'model' => $this->model,
            'content' => $this->content,
            'taskType' => $this->taskType,
            'title' => $this->title,
            'outputDimensionality' => $this->outputDimensionality,
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
     * Usage: $request->setTaskType('RETRIEVAL_QUERY') or $request->setOutputDimensionality(256)
     */
    public function __call(string $method, array $arguments): self
    {
        if (str_starts_with($method, 'set')) {
            $property = lcfirst(substr($method, 3));
            // Keep camelCase for these properties
            if ($property === 'taskType') {
                $property = 'taskType';
            } elseif ($property === 'outputDimensionality') {
                $property = 'outputDimensionality';
            }
            return $this->set($property, $arguments[0] ?? null);
        }

        throw new \BadMethodCallException("Method {$method} does not exist");
    }

    /**
     * Convenience methods using the generic set() method
     */
    public function setModel(string $model): self
    {
        // Also need to update the URL when model changes
        $newInstance = $this->set('model', $model);
        $baseUrl = config('llms.providers.drivers.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $newInstance->url = rtrim($baseUrl, '/') . '/models/' . $model . ':embedContent';
        return $newInstance;
    }

    public function setContent(array $content): self
    {
        return $this->set('content', $content);
    }

    public function setTaskType(?string $taskType): self
    {
        return $this->set('taskType', $taskType);
    }

    public function setTitle(?string $title): self
    {
        return $this->set('title', $title);
    }

    public function setOutputDimensionality(?int $outputDimensionality): self
    {
        return $this->set('outputDimensionality', $outputDimensionality);
    }

    /**
     * Content manipulation helpers
     */
    public function addTextPart(string $text): self
    {
        $content = $this->content;
        if (!isset($content['parts'])) {
            $content['parts'] = [];
        }
        $content['parts'][] = ['text' => $text];
        return $this->set('content', $content);
    }

    public function replaceText(string $text): self
    {
        return $this->set('content', ['parts' => [['text' => $text]]]);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    /**
     * Convert to array in the exact shape expected by EmbedContentEndpoint->handle()
     * Parameters must match the handle function signature exactly:
     * handle(string $url, string $apiKey, array $content, ?string $taskType, ?string $title, ?int $outputDimensionality)
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'apiKey' => $this->api_key,
            'content' => $this->content,
            'taskType' => $this->taskType,
            'title' => $this->title,
            'outputDimensionality' => $this->outputDimensionality,
        ];
    }

    public function post(): GeminiEmbeddingsResponse
    {
        try {
            // Get the parameters for the EmbedContentEndpoint
            $params = $this->toArray();

            // Make the API call using EmbedContentEndpoint
            $endpoint = new EmbedContentEndpoint();
            $rawResponse = $endpoint->handle(...$params);

            // Validate the response structure
            if (!isset($rawResponse['status_code']) || $rawResponse['status_code'] !== 200) {
                throw new \Exception(
                    'API call failed with status: ' . ($rawResponse['status_code'] ?? 'unknown') .
                    '. Error: ' . ($rawResponse['error'] ?? 'No error details provided')
                );
            }

            // Use the response body and full response data
            if (!isset($rawResponse['body']) || !is_array($rawResponse['body'])) {
                throw new \Exception('Invalid API response: missing or invalid body');
            }

            return GeminiEmbeddingsResponse::fromApiResponse($rawResponse);

        } catch (GuzzleException $e) {
            throw new \Exception('HTTP request failed: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            // Re-throw our own exceptions, wrap any others
            if (str_starts_with($e->getMessage(), 'API call failed') ||
                str_starts_with($e->getMessage(), 'Invalid API response')) {
                throw $e;
            }
            throw new \Exception('Unexpected error during API call: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Validation helpers
     */
    public function isValidTaskType(): bool
    {
        if ($this->taskType === null) {
            return true;
        }
        
        $validTaskTypes = [
            'TASK_TYPE_UNSPECIFIED',
            'RETRIEVAL_QUERY',
            'RETRIEVAL_DOCUMENT', 
            'SEMANTIC_SIMILARITY',
            'CLASSIFICATION',
            'CLUSTERING',
            'QUESTION_ANSWERING',
            'FACT_VERIFICATION',
            'CODE_RETRIEVAL_QUERY'
        ];
        
        return in_array($this->taskType, $validTaskTypes);
    }

    public function isValidOutputDimensionality(): bool
    {
        if ($this->outputDimensionality === null) {
            return true;
        }
        return $this->outputDimensionality > 0;
    }

    public function hasTitle(): bool
    {
        return $this->title !== null && trim($this->title) !== '';
    }

    public function requiresTitle(): bool
    {
        return $this->taskType === 'RETRIEVAL_DOCUMENT';
    }

    public function isValidConfiguration(): bool
    {
        // Basic validations
        if (!$this->isValidTaskType() || !$this->isValidOutputDimensionality()) {
            return false;
        }

        // Title is recommended for RETRIEVAL_DOCUMENT
        if ($this->requiresTitle() && !$this->hasTitle()) {
            return false; // Could be a warning instead
        }

        // Content must have parts with text
        if (!isset($this->content['parts']) || empty($this->content['parts'])) {
            return false;
        }

        foreach ($this->content['parts'] as $part) {
            if (!isset($part['text']) || trim($part['text']) === '') {
                return false;
            }
        }

        return true;
    }

    public function getContentTextCount(): int
    {
        if (!isset($this->content['parts'])) {
            return 0;
        }
        
        return count(array_filter($this->content['parts'], fn($part) => isset($part['text'])));
    }

    public function getTotalTextLength(): int
    {
        if (!isset($this->content['parts'])) {
            return 0;
        }

        $totalLength = 0;
        foreach ($this->content['parts'] as $part) {
            if (isset($part['text'])) {
                $totalLength += strlen($part['text']);
            }
        }
        
        return $totalLength;
    }

    public function supportsOutputDimensionality(): bool
    {
        // Output dimensionality not supported on earlier models (models/embedding-001)
        return $this->model !== 'models/embedding-001' && $this->model !== 'embedding-001';
    }

    public function supportsTaskType(): bool
    {
        // Task type not supported on earlier models (models/embedding-001)
        return $this->model !== 'models/embedding-001' && $this->model !== 'embedding-001';
    }

    public function getEstimatedTokenCount(): int
    {
        // Rough estimate: ~4 characters per token for English text
        return (int) ceil($this->getTotalTextLength() / 4);
    }
}
