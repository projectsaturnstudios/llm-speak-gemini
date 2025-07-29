<?php

namespace LLMSpeak\Gemini;

use Spatie\LaravelData\Data;

/**
 * GeminiGenerateResponse - Google Gemini API Response Handler
 * 
 * Represents the complete response structure from the Google Gemini GenerateContent API.
 * Built using the same pattern as ClaudeMessageResponse for consistency across providers.
 * 
 * Usage Examples:
 * 
 * // Create from API response
 * $response = GeminiGenerateResponse::fromApiResponse($apiData);
 * 
 * // Access response content
 * $content = $response->getTextContent();
 * $tokens = $response->getTotalTokens();
 * $wasToolUsed = $response->usedTools();
 * 
 * // Check completion status
 * if ($response->completedNaturally()) {
 *     // Handle successful completion
 * }
 * 
 * // Access thinking content (Gemini 2.0+ feature)
 * $thoughts = $response->getThinkingContent();
 * $thinkingRatio = $response->getThinkingRatio();
 * 
 * // Handle multiple candidates
 * $allCandidates = $response->getAllCandidates();
 * $bestCandidate = $response->getBestCandidate();
 */
class GeminiGenerateResponse extends Data
{
    public function __construct(
        public readonly array $candidates,
        public readonly array $usageMetadata,
        public readonly string|null $modelVersion = null,
        public readonly array|null $promptFeedback = null,
        public readonly array $headers = [],
        public readonly int $statusCode = 200,
        public readonly string|null $rawBody = null
    ) {}

    /**
     * Create instance from raw API response array
     */
    public static function fromApiResponse(array $response, array $headers = [], int $statusCode = 200, string $rawBody = null): self
    {
        return new self(
            candidates: $response['candidates'] ?? [],
            usageMetadata: $response['usageMetadata'] ?? [],
            modelVersion: $response['modelVersion'] ?? null,
            promptFeedback: $response['promptFeedback'] ?? null,
            headers: $headers,
            statusCode: $statusCode,
            rawBody: $rawBody
        );
    }

    // Content Access Methods
    
    /**
     * Get the primary text content from the first candidate
     */
    public function getTextContent(): string|null
    {
        $candidate = $this->getPrimaryCandidate();
        if (!$candidate) return null;

        $parts = $candidate['content']['parts'] ?? [];
        foreach ($parts as $part) {
            if (isset($part['text']) && !isset($part['thought'])) {
                return $part['text'];
            }
        }
        return null;
    }

    /**
     * Get all text content blocks from the first candidate
     */
    public function getAllTextContent(): array
    {
        $candidate = $this->getPrimaryCandidate();
        if (!$candidate) return [];

        $textBlocks = [];
        $parts = $candidate['content']['parts'] ?? [];
        
        foreach ($parts as $part) {
            if (isset($part['text']) && !isset($part['thought'])) {
                $textBlocks[] = $part['text'];
            }
        }
        return $textBlocks;
    }

    /**
     * Get all function/tool call blocks from the first candidate
     */
    public function getToolUseBlocks(): array
    {
        $candidate = $this->getPrimaryCandidate();
        if (!$candidate) return [];

        $toolBlocks = [];
        $parts = $candidate['content']['parts'] ?? [];
        
        foreach ($parts as $part) {
            if (isset($part['functionCall'])) {
                $toolBlocks[] = $part['functionCall'];
            }
        }
        return $toolBlocks;
    }

    /**
     * Get thinking/reasoning content from the first candidate (Gemini 2.0+ feature)
     */
    public function getThinkingContent(): array
    {
        $candidate = $this->getPrimaryCandidate();
        if (!$candidate) return [];

        $thinkingBlocks = [];
        $parts = $candidate['content']['parts'] ?? [];
        
        foreach ($parts as $part) {
            if (isset($part['thought']) && $part['thought'] === true && isset($part['text'])) {
                $thinkingBlocks[] = $part['text'];
            }
        }
        return $thinkingBlocks;
    }

    /**
     * Get safety ratings from the first candidate
     */
    public function getSafetyRatings(): array
    {
        $candidate = $this->getPrimaryCandidate();
        return $candidate['safetyRatings'] ?? [];
    }

    /**
     * Get citation metadata if available
     */
    public function getCitationMetadata(): array
    {
        $candidate = $this->getPrimaryCandidate();
        return $candidate['citationMetadata'] ?? [];
    }

    // Multi-Candidate Methods
    
    /**
     * Get all candidates (useful when candidateCount > 1)
     */
    public function getAllCandidates(): array
    {
        return $this->candidates;
    }

    /**
     * Get the best/primary candidate (first one by default)
     */
    public function getBestCandidate(): array|null
    {
        return $this->getPrimaryCandidate();
    }

    /**
     * Get text content from all candidates
     */
    public function getAllCandidatesTextContent(): array
    {
        $allText = [];
        foreach ($this->candidates as $index => $candidate) {
            $parts = $candidate['content']['parts'] ?? [];
            $candidateText = [];
            
            foreach ($parts as $part) {
                if (isset($part['text']) && !isset($part['thought'])) {
                    $candidateText[] = $part['text'];
                }
            }
            
            if (!empty($candidateText)) {
                $allText[$index] = implode('', $candidateText);
            }
        }
        return $allText;
    }

    // Status Check Methods
    
    /**
     * Check if response was stopped due to token limit
     */
    public function wasStoppedByTokenLimit(): bool
    {
        $candidate = $this->getPrimaryCandidate();
        return ($candidate['finishReason'] ?? '') === 'MAX_TOKENS';
    }

    /**
     * Check if response completed naturally
     */
    public function completedNaturally(): bool
    {
        $candidate = $this->getPrimaryCandidate();
        return ($candidate['finishReason'] ?? '') === 'STOP';
    }

    /**
     * Check if response was stopped by safety filters
     */
    public function wasBlockedBySafety(): bool
    {
        $candidate = $this->getPrimaryCandidate();
        return ($candidate['finishReason'] ?? '') === 'SAFETY';
    }

    /**
     * Check if response was stopped due to recitation detection
     */
    public function wasStoppedByRecitation(): bool
    {
        $candidate = $this->getPrimaryCandidate();
        return ($candidate['finishReason'] ?? '') === 'RECITATION';
    }

    /**
     * Check if response was stopped due to language detection
     */
    public function wasStoppedByLanguage(): bool
    {
        $candidate = $this->getPrimaryCandidate();
        return ($candidate['finishReason'] ?? '') === 'LANGUAGE';
    }

    /**
     * Check if tools/functions were used
     */
    public function usedTools(): bool
    {
        return !empty($this->getToolUseBlocks());
    }

    /**
     * Check if thinking mode was used
     */
    public function usedThinking(): bool
    {
        return $this->getThinkingTokens() > 0 || !empty($this->getThinkingContent());
    }

    /**
     * Get the finish reason from the primary candidate
     */
    public function getFinishReason(): string|null
    {
        $candidate = $this->getPrimaryCandidate();
        return $candidate['finishReason'] ?? null;
    }

    // Token Analysis Methods
    
    /**
     * Get total token count
     */
    public function getTotalTokens(): int
    {
        return $this->usageMetadata['totalTokenCount'] ?? 0;
    }

    /**
     * Get input/prompt token count
     */
    public function getInputTokens(): int
    {
        return $this->usageMetadata['promptTokenCount'] ?? 0;
    }

    /**
     * Get output/response token count
     */
    public function getOutputTokens(): int
    {
        return $this->usageMetadata['candidatesTokenCount'] ?? 0;
    }

    /**
     * Get thinking/reasoning token count (Gemini 2.0+ feature)
     */
    public function getThinkingTokens(): int
    {
        return $this->usageMetadata['thoughtsTokenCount'] ?? 0;
    }

    /**
     * Calculate thinking ratio as percentage of total tokens
     */
    public function getThinkingRatio(): float
    {
        $total = $this->getTotalTokens();
        $thinking = $this->getThinkingTokens();
        return $total > 0 ? ($thinking / $total) * 100 : 0.0;
    }

    /**
     * Check if response used cached content
     */
    public function usedCaching(): bool
    {
        return isset($this->usageMetadata['cachedContentTokenCount']) && 
               $this->usageMetadata['cachedContentTokenCount'] > 0;
    }

    /**
     * Get cached content token count
     */
    public function getCachedTokens(): int
    {
        return $this->usageMetadata['cachedContentTokenCount'] ?? 0;
    }

    /**
     * Calculate cache efficiency as percentage of input tokens
     */
    public function getCacheEfficiency(): float
    {
        $inputTokens = $this->getInputTokens();
        $cachedTokens = $this->getCachedTokens();
        return $inputTokens > 0 ? ($cachedTokens / $inputTokens) * 100 : 0.0;
    }

    // Safety Analysis Methods
    
    /**
     * Check if any safety ratings indicate potential issues
     */
    public function hasSafetyWarnings(): bool
    {
        $ratings = $this->getSafetyRatings();
        foreach ($ratings as $rating) {
            $probability = $rating['probability'] ?? 'NEGLIGIBLE';
            if (in_array($probability, ['MEDIUM', 'HIGH'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get safety warnings by category
     */
    public function getSafetyWarnings(): array
    {
        $warnings = [];
        $ratings = $this->getSafetyRatings();
        
        foreach ($ratings as $rating) {
            $probability = $rating['probability'] ?? 'NEGLIGIBLE';
            if (in_array($probability, ['MEDIUM', 'HIGH'])) {
                $warnings[] = [
                    'category' => $rating['category'] ?? 'UNKNOWN',
                    'probability' => $probability,
                    'blocked' => $rating['blocked'] ?? false
                ];
            }
        }
        return $warnings;
    }

    // Utility Methods
    
    /**
     * Get response metadata for debugging
     */
    public function getDebugInfo(): array
    {
        return [
            'model_version' => $this->modelVersion,
            'candidate_count' => count($this->candidates),
            'total_tokens' => $this->getTotalTokens(),
            'thinking_tokens' => $this->getThinkingTokens(),
            'finish_reason' => $this->getFinishReason(),
            'used_tools' => $this->usedTools(),
            'used_thinking' => $this->usedThinking(),
            'used_caching' => $this->usedCaching(),
            'safety_warnings' => $this->hasSafetyWarnings(),
            'status_code' => $this->statusCode
        ];
    }

    /**
     * Convert to array format for easy serialization
     */
    public function toResponseArray(): array
    {
        return [
            'text_content' => $this->getTextContent(),
            'tool_calls' => $this->getToolUseBlocks(),
            'thinking_content' => $this->getThinkingContent(),
            'finish_reason' => $this->getFinishReason(),
            'usage' => [
                'input_tokens' => $this->getInputTokens(),
                'output_tokens' => $this->getOutputTokens(),
                'thinking_tokens' => $this->getThinkingTokens(),
                'total_tokens' => $this->getTotalTokens(),
                'thinking_ratio' => round($this->getThinkingRatio(), 2)
            ],
            'safety' => [
                'has_warnings' => $this->hasSafetyWarnings(),
                'warnings' => $this->getSafetyWarnings()
            ],
            'metadata' => [
                'model_version' => $this->modelVersion,
                'candidate_count' => count($this->candidates),
                'used_caching' => $this->usedCaching(),
                'cache_efficiency' => round($this->getCacheEfficiency(), 2)
            ]
        ];
    }

    // Private Helper Methods
    
    /**
     * Get the primary candidate (first one)
     */
    private function getPrimaryCandidate(): array|null
    {
        return $this->candidates[0] ?? null;
    }
}
