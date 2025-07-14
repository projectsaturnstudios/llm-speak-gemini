[![Latest Version on Packagist](https://img.shields.io/packagist/v/llm-speak/google-gemini.svg?style=flat-square)](https://packagist.org/packages/llm-speak/google-gemini)
[![Total Downloads](https://img.shields.io/packagist/dt/llm-speak/google-gemini.svg?style=flat-square)](https://packagist.org/packages/llm-speak/google-gemini)

```php
use LLMSpeak\Google\Support\Facades\Gemini;

Gemini::generateContent() <--- GeminiGenerateContentAPIRepository Instance
    ->withApiKey($config['api_key']) <--- GeminiGenerateContentAPIRepository Instance
    ->withModel($model) <--- GeminiGenerateContentAPIRepository Instance
    //->withMaxTokens($max_tokens) <--- ClaudeMessagesAPIRepository Instance
    //->withSystemPrompt($prompt) <--- ClaudeMessagesAPIRepository Instance
    //->withTools($temperature) <--- ClaudeMessagesAPIRepository Instance
    //->withTemperature($temperature) <--- ClaudeMessagesAPIRepository Instance
    ->withChat($conversation) <--- ChaatEndpoint Instance
    ->handle();

Gemini::embedContent()
    ->withApikey(Gemini::api_key())
    ->withModel($model)
    ->asTaskType(GeminiTaskType::QUESTION_ANSWERING)
    ->withContent($convo)
    ->handle();

```
