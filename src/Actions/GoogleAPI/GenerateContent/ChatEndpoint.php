<?php

namespace LLMSpeak\Google\Actions\GoogleAPI\GenerateContent;

use Spatie\LaravelData\Data;
use LLMSpeak\Google\Enums\GeminiRole;
use LLMSpeak\Google\GeminiCallResult;
use Lorisleiva\Actions\Concerns\AsAction;
use LLMSpeak\Google\Support\Facades\Gemini;
use LLMSpeak\Google\Builders\SystemPromptBuilder;
use LLMSpeak\Google\Builders\ConversationBuilder;
use LLMSpeak\Google\Actions\Sagas\GenerateContent\ChatEndpoint\PrepareChatResultNode;
use LLMSpeak\Google\Actions\Sagas\GenerateContent\ChatEndpoint\PrepareChatRequestNode;
use LLMSpeak\Google\Actions\Sagas\GenerateContent\ChatEndpoint\GeminiMessagesEndpointNode;


class ChatEndpoint extends Data
{
    use AsAction;

    protected string $uri;

    public function __construct(
        public readonly string $url,
        public readonly string $api_key,
        public readonly string $model,
        public readonly array $contents,
        public readonly ?array $system_prompt = null,
        public readonly ?int $max_tokens = null,
        public readonly ?array $tools = null,
        public readonly ?float $temperature = null,
    ) {
        $this->uri = "models/{$this->model}:generateContent";
    }

    public function handle(): GeminiCallResult
    {
        $work_nodes = new PrepareChatRequestNode;
        $work_nodes->next(new GeminiMessagesEndpointNode("{$this->url}{$this->uri}"), 'call')
            ->next(new PrepareChatResultNode, 'wrap-up');

        $shared = [
            'available_parameters' => $this->toArray()
        ];

        return flow($work_nodes, $shared);
    }

    //public function stream()
    //public function structure()

    public static function test(): GeminiCallResult
    {
        $convo = (new ConversationBuilder())
            ->addText(GeminiRole::MODEL, 'Yes?')
            ->addText(GeminiRole::USER, 'What is the sky blue?')
            ->render();

        $system = (new SystemPromptBuilder())
            ->addText('You are an astrophysicist. You don\'t have time for my small talk')
            ->addText('Keep your answers to less than 20 words')
            ->render();
        // @todo - Max token

        return Gemini::generateContent()
            ->withApikey(Gemini::api_key())
            ->withModel('gemini-2.5-flash')
            ->withMaxTokens(2048)
            ->withSystemPrompt($system)
            ->withTemperature(0.7)
            ->withChat($convo)
            ->handle();
    }

    public static function test2(): GeminiCallResult
    {
        $convo = (new ConversationBuilder())
            ->addText(GeminiRole::MODEL, 'Hi!?')
            ->addText(GeminiRole::USER, 'Can you shut off the light for me?.')
            ->render();

        $system = (new SystemPromptBuilder())
            ->addText('You love to use tools.')
            ->render();

        $tools = [
            'functionDeclarations' => [
                [
                    "name" => "lights_off",
                    "description" => "Turns off the user's lights.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "off" => [
                                "type" => "boolean",
                                "description" => "Set to true",
                            ],
                        ],
                        "required" => [
                            "off",
                        ],
                    ],
                ]
            ]
        ];

        return Gemini::generateContent()
            ->withApikey(Gemini::api_key())
            ->withModel('gemini-2.5-flash')
            ->withMaxTokens(2048)
            ->withSystemPrompt($system)
            ->withTemperature(0.7)
            ->withTools($tools)
            ->withChat($convo)
            ->handle();
    }

    public static function test3(): GeminiCallResult
    {
        $convo = (new ConversationBuilder())
            ->addText(GeminiRole::MODEL, 'Hi!?')
            ->addText(GeminiRole::USER, 'Can you shut off the light for me?.')
            ->addToolRequest('lights_off', ["off" => true])
            ->addToolResult('lights_off', "The lights have been shut off. Simply tell the user 'Booyah!'")
            ->render();

        $system = (new SystemPromptBuilder())
            ->addText('You love to use tools.')
            ->render();

        $tools = [
            'functionDeclarations' => [
                [
                    "name" => "lights_off",
                    "description" => "Turns off the user's lights.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "off" => [
                                "type" => "boolean",
                                "description" => "Set to true",
                            ],
                        ],
                        "required" => [
                            "off",
                        ],
                    ],
                ]
            ]
        ];

        return Gemini::generateContent()
            ->withApikey(Gemini::api_key())
            ->withModel('gemini-2.5-flash')
            ->withMaxTokens(2048)
            ->withSystemPrompt($system)
            ->withTemperature(0.7)
            ->withTools($tools)
            ->withChat($convo)
            ->handle();
    }
}
