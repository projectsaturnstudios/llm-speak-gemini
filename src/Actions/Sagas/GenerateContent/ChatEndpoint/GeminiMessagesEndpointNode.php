<?php

namespace LLMSpeak\Google\Actions\Sagas\GenerateContent\ChatEndpoint;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use LLMSpeak\Schema\Conversation\TextMessage;
use LLMSpeak\Schema\Conversation\ToolCall;
use LLMSpeak\Schema\Conversation\ToolResult;
use ProjectSaturnStudios\PocketFlow\Node;
use Symfony\Component\VarDumper\VarDumper;

class GeminiMessagesEndpointNode extends Node
{
    public function __construct(protected string $url)
    {
        parent::__construct();
    }


    public function prep(mixed &$shared): mixed
    {
        return $shared['prepared_request'];
    }

    /**
     * @param mixed $prep_res
     * @return mixed
     * @throws ConnectionException
     */
    public function exec(mixed $prep_res): mixed
    {
        $body = $prep_res['body'];
        foreach($body['contents'] as $idx => $entry)
        {
            if($entry instanceof TextMessage)
            {
                $body['contents'][$idx] = [
                    'role' => $entry->role,
                    'parts' => [
                        'text' => $entry->content
                    ]
                ];
            }
            elseif($entry instanceof ToolCall)
            {
                $body['contents'][$idx] = [
                    'role' => 'model',
                    'parts' => [
                        'functionCall' => [
                            'name' => $entry->tool,
                            'args' => $entry->input,
                        ]
                    ]
                ];

            }
            elseif($entry instanceof ToolResult)
            {
                $body['contents'][$idx] = [
                    'role' => 'user',
                    'parts' => [
                        'functionResponse' => [
                            'name' => $entry->tool,
                            'response' => [
                                'name' => $entry->tool,
                                'content' => $entry->result
                            ]
                        ]
                    ]
                ];

            }
            else
            {
                dd(["you're not done", $entry], $body);
            }
        }

        $pre_tools = $body['tools'];
        $body['tools'] = ['functionDeclarations' => []];
        foreach($pre_tools as $idx => $tool)
        {
            $tool = [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'parameters' => $tool['input_schema']
            ];
            $body['tools']['functionDeclarations'][] = $tool;
        }

        $response = Http::withHeaders($prep_res['headers'])->post($this->url, $body);
        VarDumper::dump(["Gemini Response", $response->json()]);
        return $response->json();
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['model_response'] = $exec_res;
        return 'wrap-up';
    }
}
