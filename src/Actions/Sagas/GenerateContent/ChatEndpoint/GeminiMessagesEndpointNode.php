<?php

namespace LLMSpeak\Google\Actions\Sagas\GenerateContent\ChatEndpoint;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use ProjectSaturnStudios\PocketFlow\Node;

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
        //dd(json_encode($prep_res['body']));
        $response = Http::withHeaders($prep_res['headers'])->post($this->url, $prep_res['body']);
        return $response->json();
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['model_response'] = $exec_res;
        return 'wrap-up';
    }
}
