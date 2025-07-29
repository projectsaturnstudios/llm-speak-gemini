<?php

namespace LLMSpeak\Gemini;

use Spatie\LaravelData\Data;

/**
 * GeminiEmbeddingsResponse - Google Gemini Embeddings API Response Handler
 * 
 * Represents the complete response structure from the Google Gemini EmbedContent API.
 * Built using the same pattern as OllamaEmbeddingsResponse for consistency.
 * 
 * Google Gemini Response Structure:
 * {
 *   "embedding": {
 *     "values": [0.123, -0.456, 0.789, ...]
 *   }
 * }
 * 
 * Usage Examples:
 * 
 * // Create from API response
 * $response = GeminiEmbeddingsResponse::fromApiResponse($apiData);
 * 
 * // Access embedding data
 * $embedding = $response->getEmbedding();
 * $values = $response->getEmbeddingValues();
 * $dimensions = $response->getDimensions();
 * 
 * // Check embedding properties
 * if ($response->hasEmbedding()) {
 *     $length = $response->getEmbeddingLength();
 *     $magnitude = $response->getEmbeddingMagnitude();
 * }
 * 
 * // Get analysis
 * $summary = $response->toSummary();
 * $stats = $response->getEmbeddingStatistics();
 */
class GeminiEmbeddingsResponse extends Data
{
    public function __construct(
        public readonly ?array $embedding = null,
        public readonly int $status_code = 200,
        public readonly array $headers = []
    ) {}

    /**
     * Create instance from raw API response array
     * Expected structure: ['body' => ['embedding' => ['values' => [...]]], 'status_code' => 200, 'headers' => [...]]
     */
    public static function fromApiResponse(array $response): self
    {
        $body = $response['body'] ?? [];
        $embeddingData = $body['embedding'] ?? null;
        
        return new self(
            embedding: $embeddingData,
            status_code: $response['status_code'] ?? 200,
            headers: $response['headers'] ?? []
        );
    }

    // Core Embedding Access Methods
    
    public function getEmbedding(): ?array
    {
        return $this->embedding;
    }

    public function getEmbeddingValues(): ?array
    {
        return $this->embedding['values'] ?? null;
    }

    public function hasEmbedding(): bool
    {
        return $this->embedding !== null && !empty($this->embedding['values'] ?? []);
    }

    public function getDimensions(): ?int
    {
        $values = $this->getEmbeddingValues();
        return $values ? count($values) : null;
    }

    public function getEmbeddingLength(): ?int
    {
        return $this->getDimensions();
    }

    // Mathematical Analysis Methods
    
    public function getEmbeddingMagnitude(): ?float
    {
        $values = $this->getEmbeddingValues();
        if (!$values) {
            return null;
        }

        $sumOfSquares = array_sum(array_map(fn($x) => $x * $x, $values));
        return sqrt($sumOfSquares);
    }

    public function getEmbeddingNorm(): ?float
    {
        return $this->getEmbeddingMagnitude();
    }

    public function getNormalizedEmbedding(): ?array
    {
        $values = $this->getEmbeddingValues();
        $magnitude = $this->getEmbeddingMagnitude();
        
        if (!$values || !$magnitude || $magnitude == 0) {
            return null;
        }

        return array_map(fn($x) => $x / $magnitude, $values);
    }

    // Statistical Analysis Methods
    
    public function getEmbeddingStatistics(): ?array
    {
        $values = $this->getEmbeddingValues();
        if (!$values) {
            return null;
        }

        $count = count($values);
        $sum = array_sum($values);
        $mean = $sum / $count;
        
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;
        $stdDev = sqrt($variance);
        
        return [
            'count' => $count,
            'mean' => $mean,
            'variance' => $variance,
            'std_deviation' => $stdDev,
            'min' => min($values),
            'max' => max($values),
            'magnitude' => $this->getEmbeddingMagnitude(),
            'sum' => $sum
        ];
    }

    public function getMean(): ?float
    {
        $values = $this->getEmbeddingValues();
        if (!$values) {
            return null;
        }
        
        return array_sum($values) / count($values);
    }

    public function getStandardDeviation(): ?float
    {
        $stats = $this->getEmbeddingStatistics();
        return $stats['std_deviation'] ?? null;
    }

    public function getRange(): ?array
    {
        $values = $this->getEmbeddingValues();
        if (!$values) {
            return null;
        }

        return [
            'min' => min($values),
            'max' => max($values),
            'span' => max($values) - min($values)
        ];
    }

    // Vector Operations Methods
    
    public function dotProduct(GeminiEmbeddingsResponse $other): ?float
    {
        $values1 = $this->getEmbeddingValues();
        $values2 = $other->getEmbeddingValues();
        
        if (!$values1 || !$values2 || count($values1) !== count($values2)) {
            return null;
        }

        $dotProduct = 0;
        for ($i = 0; $i < count($values1); $i++) {
            $dotProduct += $values1[$i] * $values2[$i];
        }
        
        return $dotProduct;
    }

    public function cosineSimilarity(GeminiEmbeddingsResponse $other): ?float
    {
        $dotProduct = $this->dotProduct($other);
        $magnitude1 = $this->getEmbeddingMagnitude();
        $magnitude2 = $other->getEmbeddingMagnitude();
        
        if ($dotProduct === null || !$magnitude1 || !$magnitude2) {
            return null;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    public function euclideanDistance(GeminiEmbeddingsResponse $other): ?float
    {
        $values1 = $this->getEmbeddingValues();
        $values2 = $other->getEmbeddingValues();
        
        if (!$values1 || !$values2 || count($values1) !== count($values2)) {
            return null;
        }

        $sumOfSquaredDifferences = 0;
        for ($i = 0; $i < count($values1); $i++) {
            $sumOfSquaredDifferences += pow($values1[$i] - $values2[$i], 2);
        }
        
        return sqrt($sumOfSquaredDifferences);
    }

    // Response Status Methods
    
    public function isSuccessful(): bool
    {
        return $this->status_code >= 200 && $this->status_code < 300;
    }

    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name][0] ?? $this->headers[strtolower($name)][0] ?? null;
    }

    // Quality Assessment Methods
    
    public function hasNaNValues(): bool
    {
        $values = $this->getEmbeddingValues();
        if (!$values) {
            return false;
        }

        return !empty(array_filter($values, 'is_nan'));
    }

    public function hasInfiniteValues(): bool
    {
        $values = $this->getEmbeddingValues();
        if (!$values) {
            return false;
        }

        return !empty(array_filter($values, 'is_infinite'));
    }

    public function isValidEmbedding(): bool
    {
        return $this->hasEmbedding() && 
               !$this->hasNaNValues() && 
               !$this->hasInfiniteValues() &&
               $this->getEmbeddingMagnitude() > 0;
    }

    // Utility Methods
    
    public function toSummary(): array
    {
        $stats = $this->getEmbeddingStatistics();
        
        return [
            'has_embedding' => $this->hasEmbedding(),
            'dimensions' => $this->getDimensions(),
            'magnitude' => $this->getEmbeddingMagnitude(),
            'is_valid' => $this->isValidEmbedding(),
            'status_code' => $this->status_code,
            'statistics' => $stats,
            'response_successful' => $this->isSuccessful()
        ];
    }

    public function toArray(): array
    {
        return [
            'embedding' => $this->embedding,
            'status_code' => $this->status_code,
            'headers' => $this->headers
        ];
    }

    // Debugging & Development Methods
    
    public function getFirstNValues(int $n = 5): ?array
    {
        $values = $this->getEmbeddingValues();
        if (!$values) {
            return null;
        }

        return array_slice($values, 0, $n);
    }

    public function getLastNValues(int $n = 5): ?array
    {
        $values = $this->getEmbeddingValues();
        if (!$values) {
            return null;
        }

        return array_slice($values, -$n);
    }

    public function debugInfo(): array
    {
        return [
            'class' => self::class,
            'has_embedding' => $this->hasEmbedding(),
            'dimensions' => $this->getDimensions(),
            'status_code' => $this->status_code,
            'is_valid' => $this->isValidEmbedding(),
            'first_5_values' => $this->getFirstNValues(5),
            'last_5_values' => $this->getLastNValues(5),
            'magnitude' => $this->getEmbeddingMagnitude(),
            'mean' => $this->getMean()
        ];
    }
}
