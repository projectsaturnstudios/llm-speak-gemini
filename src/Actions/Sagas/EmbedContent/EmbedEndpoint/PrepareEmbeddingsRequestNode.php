<?php

namespace LLMSpeak\Google\Actions\Sagas\EmbedContent\EmbedEndpoint;

use ProjectSaturnStudios\PocketFlow\Node;

class PrepareEmbeddingsRequestNode extends Node
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
            'body' => [
                'model' => $prep_res['model'],

            ],
        ];

        if(array_key_exists('api_key', $prep_res)) $results['headers']['x-goog-api-key'] = $prep_res['api_key'];
        else throw new \InvalidArgumentException('API key is required for the request.');

        if(array_key_exists('content', $prep_res)) $results['body']['content'] = $prep_res['content'];
        else throw new \InvalidArgumentException('Content is required for the request.');

        if(array_key_exists('task_type', $prep_res)) $results['body']['taskType'] = $prep_res['task_type'];//->value;
        else throw new \InvalidArgumentException('TaskType is required for the request.');

        return $results;
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['prepared_request'] = $exec_res;
        return 'call';
    }
}
