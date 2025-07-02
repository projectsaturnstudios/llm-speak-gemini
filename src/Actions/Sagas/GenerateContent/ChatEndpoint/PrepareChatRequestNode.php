<?php

namespace LLMSpeak\Google\Actions\Sagas\GenerateContent\ChatEndpoint;

use ProjectSaturnStudios\PocketFlow\Node;

class PrepareChatRequestNode extends Node
{
    public function prep(mixed &$shared): mixed
    {
        return $shared['available_parameters'];
    }

    public function exec(mixed $prep_res): mixed
    {
        $results = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => [],
        ];

        if(array_key_exists('api_key', $prep_res)) $results['headers']['x-goog-api-key'] = $prep_res['api_key'];
        else throw new \InvalidArgumentException('API key is required for the request.');

        if(array_key_exists('contents', $prep_res)) $results['body']['contents'] = $prep_res['contents'];
        else throw new \InvalidArgumentException('Contents are required for the request.');

        if(array_key_exists('system_prompt', $prep_res) && (!empty($prep_res['system_prompt']))) $results['body']['system_instruction'] = $prep_res['system_prompt'];
        if(array_key_exists('tools', $prep_res) && (!empty($prep_res['tools']))) $results['body']['tools'] = $prep_res['tools'];
        if(array_key_exists('temperature', $prep_res) && (!empty($prep_res['temperature']))){
            if(!array_key_exists('generationConfig', $results['body']))$results['body']['generationConfig'] = [];
            $results['body']['generationConfig']['temperature'] = $prep_res['temperature'];
        }
        if(array_key_exists('max_tokens', $prep_res) && (!empty($prep_res['max_tokens'])))
        {
            if(!array_key_exists('generationConfig', $results['body'])) $results['body']['generationConfig'] = [];
            $results['body']['generationConfig']['maxOutputTokens'] = $prep_res['max_tokens'];
        }


        return $results;
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['prepared_request'] = $exec_res;
        return 'call';
    }
}
