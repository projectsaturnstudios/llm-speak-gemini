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
```
