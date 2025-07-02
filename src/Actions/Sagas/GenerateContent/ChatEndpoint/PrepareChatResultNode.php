<?php

namespace LLMSpeak\Google\Actions\Sagas\GenerateContent\ChatEndpoint;

use LLMSpeak\Anthropic\ClaudeCallResult;
use LLMSpeak\Google\GeminiCallResult;
use ProjectSaturnStudios\PocketFlow\Node;

class PrepareChatResultNode extends Node
{
    public function prep(mixed &$shared): mixed
    {
        return $shared['model_response'];
    }

    public function exec(mixed $prep_res): mixed
    {
        return GeminiCallResult::from($prep_res);
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared = $exec_res;
        return 'finished';
    }
}
