# LLMSpeak Google Gemini

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net/releases/)
[![Laravel](https://img.shields.io/badge/Laravel-10.x%7C11.x%7C12.x-red.svg)](https://laravel.com)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/llm-speak/google-gemini.svg?style=flat-square)](https://packagist.org/packages/llm-speak/google-gemini)
[![Total Downloads](https://img.shields.io/packagist/dt/llm-speak/google-gemini.svg?style=flat-square)](https://packagist.org/packages/llm-speak/google-gemini)

**LLMSpeak Google Gemini** is a Laravel package that provides a fluent, Laravel-native interface for integrating with Google's Gemini AI models. Built as part of the LLMSpeak ecosystem, it offers seamless integration with Laravel applications through automatic service discovery and expressive request builders.

> **Note:** This package is part of the larger [LLMSpeak ecosystem](https://github.com/projectsaturnstudios/llm-speak). For universal provider switching and standardized interfaces, check out the [LLMSpeak Core](https://github.com/projectsaturnstudios/llm-speak-core) package.

## Table of Contents
- [Features](#features)
- [Get Started](#get-started)
- [Usage](#usage)
  - [Content Generation](#content-generation)
  - [Embeddings](#embeddings)
  - [Fluent Request Building](#fluent-request-building)
  - [System Instructions](#system-instructions)
  - [Tool Calling](#tool-calling)
  - [Thinking Mode](#thinking-mode)
  - [Safety Settings](#safety-settings)
  - [Advanced Configuration](#advanced-configuration)
- [Response Handling](#response-handling)
- [Testing](#testing)
- [Credits](#credits)
- [License](#license)

## Features

- **🚀 Laravel Native**: Full Laravel integration with automatic service discovery
- **🔧 Fluent Interface**: Expressive request builders with method chaining
- **📊 Laravel Data**: Powered by Spatie Laravel Data for robust data validation
- **🛠️ Tool Support**: Complete function calling capabilities
- **🧠 Thinking Mode**: Support for Gemini 2.0+ extended thinking features
- **🛡️ Safety Controls**: Built-in safety filtering and content moderation
- **📝 Embeddings**: Full text embedding support with task-specific optimization
- **🎯 Type Safety**: Full PHP 8.2+ type declarations and IDE support
- **🔐 Secure**: Built-in API key management and request validation

## Get Started

> **Requires [PHP 8.2+](https://php.net/releases/) and Laravel 10.x/11.x/12.x**

Install the package via [Composer](https://getcomposer.org/):

```bash
composer require llm-speak/google-gemini
```

The package will automatically register itself via Laravel's package discovery.

### Environment Configuration

Add your Google API key to your `.env` file:

```env
GEMINI_API_KEY=your_google_api_key_here
```

## Usage

### Content Generation

The simplest way to generate content with Gemini:

```php
use LLMSpeak\Gemini\GeminiGenerateRequest;

$request = new GeminiGenerateRequest(
    model: 'gemini-2.0-flash-exp',
    contents: [
        [
            'parts' => [
                ['text' => 'Explain quantum computing in simple terms']
            ]
        ]
    ]
);

$response = $request->post();

echo $response->getTextContent(); // "Quantum computing is like..."
```

### Embeddings

Generate embeddings for text with task-specific optimization:

```php
use LLMSpeak\Gemini\GeminiEmbeddingsRequest;

// Simple text embedding
$request = GeminiEmbeddingsRequest::withText(
    model: 'text-embedding-004',
    text: 'Generate embeddings for this text'
);

$response = $request->post();

$embeddings = $response->getEmbeddings();
$dimensions = $response->getDimensionality();

// Task-specific embedding
$request = new GeminiEmbeddingsRequest(
    model: 'text-embedding-004',
    content: [
        'parts' => [
            ['text' => 'Research paper abstract content']
        ]
    ]
)
->setTaskType('RETRIEVAL_DOCUMENT')
->setTitle('Research Paper: AI in Healthcare')
->setOutputDimensionality(768);

$response = $request->post();
```

### Universal LLMSpeak Interface

For **provider-agnostic embeddings** that work across Gemini, Mistral, Ollama, and other providers, use the universal LLMSpeak interface:

```php
use LLMSpeak\Core\Support\Facades\LLMSpeak;
use LLMSpeak\Core\Support\Requests\LLMSpeakEmbeddingsRequest;

// Universal request works with ANY provider
$request = new LLMSpeakEmbeddingsRequest(
    model: 'text-embedding-004',
    input: 'Generate embeddings for this text',
    encoding_format: null,           // Optional: 'float' or 'base64'
    dimensions: null,                // Optional: Custom dimensions (Matryoshka)
    task_type: 'SEMANTIC_SIMILARITY' // Optional: Gemini task optimization
);

// Execute with Gemini - same code works with other providers!
$response = LLMSpeak::embeddingsFrom('gemini', $request);

// Universal response methods
$embeddings = $response->getAllEmbeddings();
$firstVector = $response->getFirstEmbedding();
$dimensions = $response->getDimensions();
$tokenUsage = $response->getTotalTokens();
```

### Advanced Universal Features

Leverage Gemini's unique capabilities through the universal interface:

```php
// Task-specific optimization
$request = new LLMSpeakEmbeddingsRequest(
    model: 'text-embedding-004',
    input: 'Research paper about artificial intelligence',
    encoding_format: 'float',
    dimensions: 768,                    // Matryoshka representation  
    task_type: 'RETRIEVAL_DOCUMENT'    // Gemini-specific optimization
);

$response = LLMSpeak::embeddingsFrom('gemini', $request);

// Batch processing with universal interface
$batchRequest = new LLMSpeakEmbeddingsRequest(
    model: 'text-embedding-004', 
    input: [
        'Document one content',
        'Document two content', 
        'Document three content'
    ],
    encoding_format: 'float',
    dimensions: 512,
    task_type: 'CLUSTERING'
);

$batchResponse = LLMSpeak::embeddingsFrom('gemini', $batchRequest);

echo "Generated {$batchResponse->getEmbeddingCount()} embeddings";
echo "Vector dimensions: {$batchResponse->getDimensions()}";
```

### Why Use Universal Interface?

**✅ Provider Independence:** Switch between Gemini, Mistral, Ollama with zero code changes  
**✅ Future Proof:** New providers automatically supported  
**✅ Consistent API:** Same methods across all providers  
**✅ Type Safety:** Full PHP type declarations and IDE support  
**✅ Best of Both:** Access provider-specific features when needed  

```php
// Same request works with different providers!
$request = new LLMSpeakEmbeddingsRequest(/*...*/);

$geminiResponse = LLMSpeak::embeddingsFrom('gemini', $request);   // Google AI
$mistralResponse = LLMSpeak::embeddingsFrom('mistral', $request); // Mistral AI  
$ollamaResponse = LLMSpeak::embeddingsFrom('ollama', $request);   // Local models
```

### Fluent Request Building

Build complex requests using the fluent interface:

```php
use LLMSpeak\Gemini\GeminiGenerateRequest;

$request = new GeminiGenerateRequest(
    model: 'gemini-2.0-flash-exp',
    contents: [
        [
            'parts' => [
                ['text' => 'Write a creative story about space exploration']
            ]
        ]
    ]
)
->setTemperature(0.9)
->setMaxOutputTokens(2048)
->setTopP(0.8)
->setTopK(40)
->setResponseMimeType('application/json')
->setStopSequences(['THE END']);

$response = $request->post();

// Access response properties
echo $response->getTextContent();
echo $response->getTotalTokens();
echo $response->getModelVersion();
```

### Batch Configuration

Set multiple parameters at once:

```php
$request = new GeminiGenerateRequest(
    model: 'gemini-2.0-flash-exp',
    contents: $conversation
)->setMultiple([
    'temperature' => 0.7,
    'maxOutputTokens' => 1024,
    'topP' => 0.9,
    'topK' => 50,
    'stopSequences' => ['Human:', 'Assistant:']
]);
```

### System Instructions

Provide system-level instructions to guide model behavior:

```php
$request = new GeminiGenerateRequest(
    model: 'gemini-2.0-flash-exp',
    contents: [
        [
            'parts' => [
                ['text' => 'Explain machine learning concepts']
            ]
        ]
    ]
)->setSystemInstruction([
    'parts' => [
        [
            'text' => 'You are an expert computer science professor. ' .
                     'Provide detailed explanations with examples and analogies. ' .
                     'Always include practical applications.'
        ]
    ]
]);

$response = $request->post();
```

### Tool Calling

Enable Gemini to use external functions and tools:

```php
$tools = [
    'functionDeclarations' => [
        [
            'name' => 'get_current_weather',
            'description' => 'Get the current weather for a specific location',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'location' => [
                        'type' => 'string',
                        'description' => 'The city and state/country'
                    ],
                    'unit' => [
                        'type' => 'string',
                        'enum' => ['celsius', 'fahrenheit']
                    ]
                ],
                'required' => ['location']
            ]
        ]
    ]
];

$request = new GeminiGenerateRequest(
    model: 'gemini-2.0-flash-exp',
    contents: [
        [
            'parts' => [
                ['text' => 'What\'s the weather like in Tokyo?']
            ]
        ]
    ]
)
->setTools($tools)
->setToolConfig([
    'functionCallingConfig' => [
        'mode' => 'AUTO'
    ]
]);

$response = $request->post();

// Check for tool usage
if ($response->usedTools()) {
    $toolCalls = $response->getToolCalls();
    foreach ($toolCalls as $call) {
        echo "Function: {$call['name']}\n";
        echo "Arguments: " . json_encode($call['args']) . "\n";
    }
}
```

### Thinking Mode

Enable Gemini 2.0+ extended thinking capabilities:

```php
$request = new GeminiGenerateRequest(
    model: 'gemini-2.0-flash-thinking-exp',
    contents: [
        [
            'parts' => [
                ['text' => 'Solve this complex math problem: What is the derivative of x^3 + 2x^2 - 5x + 3?']
            ]
        ]
    ]
)
->setThinkingBudget(1024)  // Allow up to 1024 thinking tokens
->setIncludeThoughts(true); // Include reasoning in response

$response = $request->post();

// Access thinking content
$thoughts = $response->getThinkingContent();
$finalAnswer = $response->getTextContent();
$thinkingRatio = $response->getThinkingRatio();

echo "Reasoning: " . $thoughts;
echo "Answer: " . $finalAnswer;
echo "Thinking used: " . ($thinkingRatio * 100) . "% of budget";
```

### Safety Settings

Configure content safety and filtering:

```php
$request = new GeminiGenerateRequest(
    model: 'gemini-2.0-flash-exp',
    contents: $contents
)->setSafetySettings([
    [
        'category' => 'HARM_CATEGORY_HATE_SPEECH',
        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
    ],
    [
        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
        'threshold' => 'BLOCK_ONLY_HIGH'
    ]
]);

$response = $request->post();

// Check safety feedback
$promptFeedback = $response->getPromptFeedback();
if ($promptFeedback && isset($promptFeedback['blockReason'])) {
    echo "Content blocked: " . $promptFeedback['blockReason'];
}
```

### Advanced Configuration

Configure advanced parameters for optimal performance:

```php
$request = new GeminiGenerateRequest(
    model: 'gemini-2.0-flash-exp',
    contents: $conversation
)
->setTemperature(0.8)
->setMaxOutputTokens(4096)
->setTopP(0.95)
->setTopK(64)
->setResponseMimeType('application/json')
->setResponseSchema($jsonSchema)
->setCachedContent($cachedContentName)
->setPresencePenalty(0.2)
->setFrequencyPenalty(0.3);

$response = $request->post();
```

## Response Handling

Access comprehensive response data:

```php
$response = $request->post();

// Basic response info
$textContent = $response->getTextContent();
$allCandidates = $response->getAllCandidates();
$bestCandidate = $response->getBestCandidate();

// Token usage
$totalTokens = $response->getTotalTokens();
$inputTokens = $response->getInputTokens();
$outputTokens = $response->getOutputTokens();
$cachedTokens = $response->getCachedContentTokenCount();

// Model information
$modelVersion = $response->getModelVersion();
$finishReason = $response->getFinishReason();

// Safety and feedback
$promptFeedback = $response->getPromptFeedback();
$safetyRatings = $response->getSafetyRatings();

// Tool usage
$usedTools = $response->usedTools();
$toolCalls = $response->getToolCalls();

// Thinking mode (Gemini 2.0+)
$thinkingContent = $response->getThinkingContent();
$thinkingRatio = $response->getThinkingRatio();

// Completion status
$completedNaturally = $response->completedNaturally();
$hitTokenLimit = $response->reachedTokenLimit();
$wasBlocked = $response->wasBlockedBySafety();

// Convert to array for storage
$responseArray = $response->toArray();
```

## Testing

The package provides testing utilities for mocking Gemini responses:

```php
use LLMSpeak\Gemini\GeminiGenerateRequest;
use LLMSpeak\Gemini\GeminiGenerateResponse;

// Create a mock response
$mockResponse = new GeminiGenerateResponse(
    candidates: [
        [
            'content' => [
                'parts' => [
                    ['text' => 'Mock response content']
                ]
            ],
            'finishReason' => 'STOP'
        ]
    ],
    usageMetadata: [
        'promptTokenCount' => 10,
        'candidatesTokenCount' => 15,
        'totalTokenCount' => 25
    ],
    modelVersion: 'gemini-2.0-flash-exp'
);

// Test your application logic
$this->assertEquals('Mock response content', $mockResponse->getTextContent());
$this->assertEquals(25, $mockResponse->getTotalTokens());
```

## Credits

- [Project Saturn Studios](https://github.com/projectsaturnstudios)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

**Part of the LLMSpeak Ecosystem** - Made with ADHD by [Project Saturn Studios](https://projectsaturnstudios.com)
