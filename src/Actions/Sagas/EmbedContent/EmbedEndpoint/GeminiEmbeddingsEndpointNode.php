<?php

namespace LLMSpeak\Google\Actions\Sagas\EmbedContent\EmbedEndpoint;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use LLMSpeak\Schema\Conversation\TextMessage;
use LLMSpeak\Schema\Conversation\ToolCall;
use LLMSpeak\Schema\Conversation\ToolResult;
use ProjectSaturnStudios\PocketFlow\Node;
use Symfony\Component\VarDumper\VarDumper;

class GeminiEmbeddingsEndpointNode extends Node
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
        VarDumper::dump(['Gemini Request - GeminiEmbeddingsEndpointNode- '.$this->url, json_encode($prep_res['body'])]);
        $response = Http::withHeaders($prep_res['headers'])->post($this->url, $prep_res['body']);
        VarDumper::dump(["Gemini Response - GeminiEmbeddingsEndpointNode", $response->json()]);
        return $response->json();
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['model_response'] = $exec_res;
        return 'wrap-up';
    }
}
